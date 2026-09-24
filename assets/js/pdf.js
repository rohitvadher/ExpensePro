var PDFEngine = {
    ensureLib: function () {
        if (typeof html2pdf !== 'undefined') {
            return Promise.resolve(true);
        }
        
        if (window.ExpensePro && ExpensePro.vendor && ExpensePro.vendor.loadScript && typeof VendorCDN !== 'undefined') {
            return ExpensePro.vendor.loadScript(VendorCDN.html2pdf).then(function () {
                return typeof html2pdf !== 'undefined';
            }).catch(function () { return false; });
        }
        return Promise.resolve(false);
    },

    filename: function (kind, dateFrom, dateTo) {
        var safe = function (s) { return String(s || '').replace(/[^0-9A-Za-z-]/g, ''); };
        return 'ExpensePro_' + kind + '_' + safe(dateFrom) + '_to_' + safe(dateTo) + '.pdf';
    },

    host: function () {
        var self = this;
        var frame = document.getElementById('ep-pdf-frame');
        if (frame && frame.contentDocument && frame.contentDocument.getElementById('ep-pdf-root')) {
            return Promise.resolve(frame.contentDocument.getElementById('ep-pdf-root'));
        }
        return new Promise(function (resolve) {
            if (!frame) {
                frame = document.createElement('iframe');
                frame.id = 'ep-pdf-frame';
                frame.title = 'PDF render surface';
                frame.setAttribute('aria-hidden', 'true');
                frame.setAttribute('tabindex', '-1');
                frame.style.cssText =
                    'position:fixed;top:0;left:-10000px;width:820px;height:100px;' +
                    'border:0;padding:0;margin:0;pointer-events:none;';
                document.body.appendChild(frame);
            }
            var doc = frame.contentDocument;
            var boot = function () {
                try {
                    var d = frame.contentDocument;
                    if (!d.getElementById('ep-pdf-style')) {
                        var style = d.createElement('style');
                        style.id = 'ep-pdf-style';
                        style.textContent = self._collectCss();
                        d.head.appendChild(style);
                    }
                    d.body.style.margin = '0';
                    d.body.style.background = '#ffffff';
                    var root = d.getElementById('ep-pdf-root');
                    if (!root) {
                        root = d.createElement('div');
                        root.id = 'ep-pdf-root';
                        d.body.appendChild(root);
                    }
                    resolve(root);
                } catch (e) {
                    resolve(null);
                }
            };
            if (doc && doc.readyState === 'complete' && doc.getElementById('ep-pdf-root')) {
                boot();
            } else {
                frame.onload = boot;
                doc.open();
                doc.write('<!DOCTYPE html><html><head><meta charset="utf-8"></head><body></body></html>');
                doc.close();
            }
        });
    },

    _collectCss: function () {
        var css = '';
        try {
            Array.prototype.forEach.call(document.styleSheets, function (sheet) {
                try {
                    if (!sheet.cssRules) return;
                    Array.prototype.forEach.call(sheet.cssRules, function (rule) {
                        css += rule.cssText + '\n';
                    });
                } catch (e) {}
            });
        } catch (e) {}
        return css;
    },

    render: function (buildHtml, filename) {
        var self = this;
        return this.host().then(function (root) {
            if (!root) return false;
            var html = '';
            try {
                html = buildHtml();
            } catch (e) {
                return false;
            }
            root.innerHTML = html;
            return self.save(root, filename);
        }).catch(function () { return false; });
    },

    
    renderReport: function (coverHtml, historyRows, filename, countNote) {
        var self = this;
        return this.host().then(function (root) {
            if (!root) return false;
            root.innerHTML = coverHtml;
            return self.save(root, filename, function (pdf) {
                self._drawHistoryPages(pdf, historyRows || [], countNote || '');
            });
        }).catch(function () { return false; });
    },

    _rupeeText: function (amount) {
        
        var formatted = (typeof formatCurrency === 'function')
            ? formatCurrency(amount)
            : String(amount);
        return formatted.replace(/₹/g, 'Rs. ');
    },

    _drawHistoryPages: function (pdf, rows, countNote) {
        if (!rows || rows.length === 0) return;
        var pageW = pdf.internal.pageSize.getWidth();
        var pageH = pdf.internal.pageSize.getHeight();
        var left = 14;
        var right = pageW - 14;
        var usable = right - left;
        var colDate = left;
        var colType = left + 26;
        var colCat = left + 48;
        var colAmtR = left + 128;
        var colDesc = left + 132;
        var rowsPerPage = 35;
        var yTop = 30;
        var pitch = 7;

        function toWinAnsi(text) {
            
            
            return String(text == null ? '' : text)
                .replace(/₹/g, 'Rs. ')
                .replace(/[–—]/g, '-')
                .replace(/[‘’]/g, "'")
                .replace(/[“”]/g, '"')
                .replace(/…/g, '...')
                .replace(/[^\x20-\x7E\xA0-\xFF]/g, '');
        }
        function truncate(text, max) {
            text = toWinAnsi(text);
            return text.length > max ? text.slice(0, max - 3) + '...' : text;
        }

        for (var start = 0; start < rows.length; start += rowsPerPage) {
            pdf.addPage();
            var y = 14;
            pdf.setFont('helvetica', 'bold');
            pdf.setFontSize(11);
            pdf.setTextColor(0, 0, 0);
            pdf.text(start === 0 ? 'Transaction History' : 'Transaction History (cont.)', left, y);
            y += 2;
            pdf.setFillColor(0, 0, 0);
            pdf.rect(left, y, usable, 8, 'F');
            pdf.setTextColor(255, 255, 255);
            pdf.setFontSize(8);
            pdf.text('DATE', colDate, y + 5.5);
            pdf.text('TYPE', colType, y + 5.5);
            pdf.text('CATEGORY', colCat, y + 5.5);
            pdf.text('AMOUNT', colAmtR, y + 5.5, { align: 'right' });
            pdf.text('DESCRIPTION', colDesc, y + 5.5);
            y = yTop;
            pdf.setFont('helvetica', 'normal');
            pdf.setTextColor(0, 0, 0);
            var chunk = rows.slice(start, start + rowsPerPage);
            for (var i = 0; i < chunk.length; i++) {
                var tx = chunk[i] || {};
                var income = tx.type === 'income';
                var amount = (income ? '+' : '-') + this._rupeeText(tx.amount);
                pdf.text(truncate(tx.date || '', 12), colDate, y);
                pdf.text(income ? 'Income' : 'Expense', colType, y);
                pdf.text(truncate(tx.category || tx.category_name || '-', 22), colCat, y);
                pdf.text(amount, colAmtR, y, { align: 'right' });
                pdf.text(truncate(tx.description || '-', 34), colDesc, y);
                pdf.setDrawColor(220, 220, 220);
                pdf.line(left, y + 2.2, right, y + 2.2);
                y += pitch;
            }
            if (start + rowsPerPage >= rows.length && countNote) {
                pdf.setFontSize(8);
                pdf.setTextColor(100, 100, 100);
                pdf.text(countNote, right, y + 4, { align: 'right' });
            }
        }
    },

    save: function (element, filename, extra) {
        if (!element) return Promise.resolve(false);
        
        
        function settled() {
            return new Promise(function (resolve) {
                var done = false;
                function finish() {
                    if (done) return;
                    done = true;
                    setTimeout(resolve, 400);
                }
                try {
                    var frame = document.getElementById('ep-pdf-frame');
                    var docs = [document];
                    if (frame && frame.contentDocument) docs.push(frame.contentDocument);
                    var pending = docs.length;
                    docs.forEach(function (d) {
                        try {
                            if (d.fonts && d.fonts.ready && typeof d.fonts.ready.then === 'function') {
                                d.fonts.ready.then(function () { if (--pending === 0) finish(); }, function () { if (--pending === 0) finish(); });
                            } else if (--pending === 0) {
                                finish();
                            }
                        } catch (e) {
                            if (--pending === 0) finish();
                        }
                    });
                } catch (e) {
                    finish();
                }
                setTimeout(finish, 2500);
            });
        }
        var self = this;
        return settled().then(function () {
            return self.ensureLib();
        }).then(function (ok) {
            if (!ok) return false;
            var opt = {
                margin: [6, 6, 8, 6],
                filename: filename,
                image: { type: 'jpeg', quality: 0.95 },
                
                
                html2canvas: { scale: 1, useCORS: true },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
                pagebreak: { mode: ['css', 'legacy'] }
            };
            return html2pdf().set(opt).from(element).toPdf().get('pdf').then(function (pdf) {
                if (typeof extra === 'function') {
                    try { extra(pdf); } catch (e) {}
                }
                var total = pdf.internal.getNumberOfPages();
                for (var i = 1; i <= total; i++) {
                    pdf.setPage(i);
                    pdf.setFontSize(8);
                    pdf.setTextColor(100, 100, 100);
                    pdf.text('Page ' + i + ' of ' + total, pdf.internal.pageSize.getWidth() / 2, pdf.internal.pageSize.getHeight() - 3, { align: 'center' });
                }
            }).save().then(function () {
                showToast('PDF downloaded!', 'success');
                return true;
            });
        }).catch(function () {
            showToast('PDF generation failed.', 'error');
            return false;
        }).then(function (result) {
            try { if (element) element.innerHTML = ''; } catch (e) {}
            return result;
        });
    },

    fetchAllPages: function (baseUrl) {
        var all = [];
        var page = 1;
        function next() {
            var sep = baseUrl.indexOf('?') === -1 ? '?' : '&';
            return ajaxRequest('GET', baseUrl + sep + 'page=' + page + '&limit=50').then(function (resp) {
                if (!resp || !resp.success || !resp.data) {
                    throw { success: false, message: 'Could not load all rows for the PDF.' };
                }
                var rows = resp.data.transactions || [];
                all = all.concat(rows);
                var pagination = resp.data.pagination || {};
                var totalPages = pagination.total_pages || 1;
                page++;
                if (page <= totalPages && page <= 40) return next();
                return all;
            });
        }
        return next();
    },

    docHeader: function (title, subtitle) {
        return '<div class="pdf-banner">' +
            '<p class="pdf-brand">ExpensePro</p>' +
            '<h1 class="pdf-title">' + escapeHtml(title) + '</h1>' +
            '<p class="pdf-subtitle">' + escapeHtml(subtitle) + '</p>' +
            '</div>';
    },

    summaryGrid: function (summary) {
        var s = summary || {};
        var income = Number(s.total_income) || 0;
        var expense = Number(s.total_expense) || 0;
        var balance = (s.balance !== undefined && s.balance !== null) ? Number(s.balance) : income - expense;
        var positive = balance >= 0;
        return '<div class="pdf-summary">' +
            '<div class="pdf-summary-card"><div class="pdf-kicker">Income</div>' +
            '<div class="pdf-big">' + formatCurrency(income) + '</div></div>' +
            '<div class="pdf-summary-card"><div class="pdf-kicker">Expenses</div>' +
            '<div class="pdf-big">' + formatCurrency(expense) + '</div></div>' +
            '<div class="pdf-summary-card"><div class="pdf-kicker">Net Savings</div>' +
            '<div class="pdf-big ' + (positive ? 'pdf-pos' : 'pdf-neg') + '">' +
            (positive ? '+' : '-') + formatCurrency(Math.abs(balance)) + '</div>' +
            (s.savings_rate !== undefined
                ? '<div class="pdf-note">' + escapeHtml(String(s.savings_rate)) + '% savings rate</div>'
                : '') +
            '</div></div>';
    },

    categoryTable: function (cats) {
        cats = cats || [];
        if (cats.length === 0) {
            return '<p class="pdf-empty">No expense data for this period.</p>';
        }
        return this._categoryTableChunk(cats);
    },

    _categoryTableChunk: function (cats) {
        var rows = '';
        cats.forEach(function (cat) {
            rows +=
                '<tr>' +
                '<td>' + escapeHtml(cat.name) + '</td>' +
                '<td class="pdf-num pdf-strong">' + formatCurrency(cat.total) + '</td>' +
                '<td class="pdf-num pdf-muted">' + escapeHtml(String(cat.percentage)) + '%</td>' +
                '<td class="pdf-bar-cell"><div class="pdf-bar-track">' +
                '<div class="pdf-bar-fill" style="width:' + Math.min(100, Math.max(0, Number(cat.percentage) || 0)) + '%"></div>' +
                '</div></td></tr>';
        });
        return '<table class="pdf-table">' +
            '<thead><tr><th>Category</th>' +
            '<th class="pdf-num">Amount</th>' +
            '<th class="pdf-num">Share</th>' +
            '<th>Progress</th></tr></thead><tbody>' + rows + '</tbody></table>';
    },

    sectionTitle: function (title) {
        return '<h2 class="pdf-section-title">' + escapeHtml(title) + '</h2>';
    },

    docFooter: function () {
        var dateStr = new Date().toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' });
        return '<div class="pdf-footer">' +
            '<p>ExpensePro | Generated ' + dateStr + '</p></div>';
    },

    wrap: function (inner) {
        return '<div class="pdf-doc">' + inner + this.docFooter() + '</div>';
    }
};

window.ExpensePro.utils.PDFEngine = PDFEngine;
