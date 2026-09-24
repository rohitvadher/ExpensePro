var parsedCSV = [];
var selectedFile = null;

document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('choose-file-btn').addEventListener('click', function (e) {
        e.stopPropagation();
        document.getElementById('csv-file-input').click();
    });

    document.getElementById('upload-area').addEventListener('click', function () {
        document.getElementById('csv-file-input').click();
    });

    document.getElementById('csv-file-input').addEventListener('change', function (e) {
        if (this.files && this.files.length > 0) {
            selectedFile = this.files[0];
            handleFileSelected(selectedFile);
        }
    });

    var uploadArea = document.getElementById('upload-area');
    uploadArea.addEventListener('dragover', function (e) {
        e.preventDefault();
        this.classList.add('border-indigo-500', 'bg-indigo-50/50');
    });
    uploadArea.addEventListener('dragleave', function (e) {
        e.preventDefault();
        this.classList.remove('border-indigo-500', 'bg-indigo-50/50');
    });
    uploadArea.addEventListener('drop', function (e) {
        e.preventDefault();
        this.classList.remove('border-indigo-500', 'bg-indigo-50/50');
        if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
            selectedFile = e.dataTransfer.files[0];
            handleFileSelected(selectedFile);
        }
    });

    document.getElementById('confirm-import-btn').addEventListener('click', confirmImport);
    document.getElementById('cancel-import-btn').addEventListener('click', cancelImport);
    document.getElementById('result-dismiss-btn').addEventListener('click', function () {
        document.getElementById('import-result').classList.add('hidden');
    });

    loadImportHistory();
});

function handleFileSelected(file) {
    var ext = file.name.split('.').pop().toLowerCase();
    if (ext !== 'csv') {
        showToast('Please select a CSV (.csv) file.', 'error');
        return;
    }

    if (file.size > 5 * 1024 * 1024) {
        showToast('File size must be less than 5 MB.', 'error');
        return;
    }

    document.getElementById('file-name-display').textContent = 'Selected: ' + file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
    document.getElementById('file-name-display').classList.remove('hidden');

    parseCSV(file);
}

function parseCSV(file) {
    var reader = new FileReader();
    reader.onload = function (e) {
        var rows = parseCSVRows(String(e.target.result || ''));

        if (rows.length === 0) {
            showToast('The CSV file is empty.', 'error');
            return;
        }

        parsedCSV = [];
        var startIndex = 0;

        var firstLine = rows[0];
        var knownHeaders = ['date', 'type', 'category', 'amount', 'description'];
        var isHeader = firstLine.some(function (col) {
            return knownHeaders.indexOf(String(col || '').toLowerCase().trim()) !== -1;
        });

        if (isHeader) { startIndex = 1; }

        for (var i = startIndex; i < rows.length; i++) {
            var row = rows[i];
            if (row.length === 0 || (row.length === 1 && String(row[0]).trim() === '')) {
                continue;
            }
            if (row.length >= 4) {
                parsedCSV.push({
                    date: row[0] ? String(row[0]).trim() : '',
                    type: row[1] ? String(row[1]).trim().toLowerCase() : '',
                    category: row[2] ? String(row[2]).trim() : '',
                    amount: row[3] ? String(row[3]).trim() : '',
                    description: row[4] ? String(row[4]).trim() : ''
                });
            }
        }

        if (parsedCSV.length === 0) {
            showToast('No valid rows found in the CSV file.', 'error');
            return;
        }

        showPreview(parsedCSV);
    };
    reader.readAsText(file);
}

function parseCSVRows(text) {
    var rows = [];
    var row = [];
    var current = '';
    var inQuotes = false;

    for (var i = 0; i < text.length; i++) {
        var ch = text[i];
        if (inQuotes) {
            if (ch === '"') {
                if (text[i + 1] === '"') {
                    current += '"';
                    i++;
                } else {
                    inQuotes = false;
                }
            } else {
                current += ch;
            }
        } else if (ch === '"') {
            inQuotes = true;
        } else if (ch === ',') {
            row.push(current);
            current = '';
        } else if (ch === '\r') {
        } else if (ch === '\n') {
            row.push(current);
            rows.push(row);
            row = [];
            current = '';
        } else {
            current += ch;
        }
    }

    row.push(current);
    if (!(row.length === 1 && row[0] === '') || rows.length === 0) {
        rows.push(row);
    }
    return rows.filter(function (entry) {
        return !(entry.length === 1 && String(entry[0]).trim() === '');
    });
}

function parseCSVLine(line) {
    return parseCSVRows(line)[0] || [];
}

function showPreview(rows) {
    document.getElementById('preview-section').classList.remove('hidden');
    document.getElementById('preview-count').textContent = rows.length + ' row(s)';

    var tbody = document.getElementById('preview-table-body');
    var html = '';
    rows.forEach(function (row, index) {
        var isValid = validateRow(row);
        var statusClass = isValid ? 'text-emerald-600 bg-emerald-50' : 'text-rose-600 bg-rose-50';
        var statusText = isValid ? 'Ready' : 'Error';

        html +=
            '<tr class="border-t border-slate-50 hover:bg-slate-50/50">' +
            '<td class="px-4 py-2 text-slate-400">' + (index + 1) + '</td>' +
            '<td class="px-4 py-2 text-slate-700 font-mono">' + escapeHtml(row.date) + '</td>' +
            '<td class="px-4 py-2"><span class="inline-block text-xs px-2 py-0.5 rounded-full ' + (row.type === 'income' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600') + '">' + escapeHtml(row.type) + '</span></td>' +
            '<td class="px-4 py-2 text-slate-700">' + escapeHtml(row.category) + '</td>' +
            '<td class="px-4 py-2 text-slate-900 font-medium">' + escapeHtml(row.amount) + '</td>' +
            '<td class="px-4 py-2 text-slate-400 max-w-[120px] truncate">' + escapeHtml(row.description || '\u2014') + '</td>' +
            '<td class="px-4 py-2"><span class="inline-block text-xs px-2 py-0.5 rounded-full ' + statusClass + '">' + escapeHtml(statusText) + '</span></td>' +
            '</tr>';
    });

    tbody.innerHTML = html;
}

function validateRow(row) {
    if (!normalizeImportDate(row.date)) return false;
    if (row.type !== 'income' && row.type !== 'expense') return false;
    if (!row.category) return false;
    return cleanImportAmount(row.amount) !== null;
}

function normalizeImportDate(raw) {
    var value = String(raw || '').trim();
    var match = value.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/)
        || value.match(/^(\d{1,2})-(\d{1,2})-(\d{4})$/);
    if (match) {
        value = match[3] + '-' + String(match[2]).padStart(2, '0') + '-' + String(match[1]).padStart(2, '0');
    } else {
        var isoSlash = value.match(/^(\d{4})\/(\d{1,2})\/(\d{1,2})$/);
        if (isoSlash) {
            value = isoSlash[1] + '-' + String(isoSlash[2]).padStart(2, '0') + '-' + String(isoSlash[3]).padStart(2, '0');
        }
    }
    if (!/^\d{4}-\d{2}-\d{2}$/.test(value)) return null;
    var parts = value.split('-');
    var date = new Date(Number(parts[0]), Number(parts[1]) - 1, Number(parts[2]));
    if (date.getFullYear() !== Number(parts[0]) || (date.getMonth() + 1) !== Number(parts[1]) || date.getDate() !== Number(parts[2])) {
        return null;
    }
    return value;
}

function cleanImportAmount(raw) {
    var cleaned = String(raw || '').replace(/[$,\u20B9,\s]/g, '').trim();
    if (cleaned === '' || !isFinite(Number(cleaned))) return null;
    var value = Number(cleaned);
    if (!(value > 0) || value > 9999999999.99) return null;
    if (Math.abs((value * 100) - Math.round(value * 100)) > 0.000001) return null;
    return Math.round(value * 100) / 100;
}

function confirmImport() {
    if (!selectedFile || parsedCSV.length === 0) {
        showToast('No data to import.', 'error');
        return;
    }

    var btn = document.getElementById('confirm-import-btn');
    showButtonLoading('confirm-import-btn');

    var formData = new FormData();
    formData.append('csv_file', selectedFile);

    apiUpload(BASE_URL + 'api/import.php', formData)
    .then(function (response) {
        hideButtonLoading('confirm-import-btn');
        showImportResult((response && response.data) || {});
        document.getElementById('preview-section').classList.add('hidden');
        document.getElementById('file-name-display').classList.add('hidden');
        selectedFile = null;
        parsedCSV = [];
        loadImportHistory();
        showToast(response.message || 'Import completed.', 'success');
    })
    .catch(function (error) {
        hideButtonLoading('confirm-import-btn');
        showToast(error.message || 'Import failed.', 'error');
    });
}

function showImportResult(data) {
    document.getElementById('import-result').classList.remove('hidden');
    document.getElementById('result-total').textContent = data.total || 0;
    document.getElementById('result-imported').textContent = data.imported || 0;
    document.getElementById('result-skipped').textContent = data.skipped || 0;
    document.getElementById('result-errors').textContent = (data.errors && data.errors.length) || 0;

    var errorList = document.getElementById('result-errors-list');
    if (data.errors && data.errors.length > 0) {
        var html = '<p class="text-xs font-medium text-rose-600 mb-2">Row Errors:</p>';
        data.errors.forEach(function (err) {
            var messages = Array.isArray(err.errors) ? err.errors.map(function (message) { return escapeHtml(String(message)); }) : [];
            html += '<div class="text-xs text-rose-500 bg-rose-50 rounded-lg px-3 py-1.5">Row ' + escapeHtml(String(err.row)) + ': ' + messages.join(', ') + '</div>';
        });
        errorList.innerHTML = html;
    } else {
        errorList.innerHTML = '';
    }
}

function cancelImport() {
    document.getElementById('preview-section').classList.add('hidden');
    document.getElementById('file-name-display').classList.add('hidden');
    document.getElementById('csv-file-input').value = '';
    selectedFile = null;
    parsedCSV = [];
}

var importHistoryAbort = null;
var importHistorySeq = 0;

function loadImportHistory() {
    var container = document.getElementById('import-history-list');
    if (!container) return;
    if (importHistoryAbort) {
        try { importHistoryAbort.abort(); } catch (e) {}
    }
    importHistoryAbort = ('AbortController' in window) ? new AbortController() : null;
    var signal = importHistoryAbort ? importHistoryAbort.signal : undefined;
    var mySeq = ++importHistorySeq;

    ajaxRequest('GET', BASE_URL + 'api/import.php?action=history', null, { signal: signal })
        .then(function (response) {
            if (mySeq !== importHistorySeq) return;
            if (response.success && response.data && response.data.length > 0) {
                var html = '';
                response.data.forEach(function (rec) {
                    html +=
                        '<div class="flex items-center justify-between bg-slate-50 rounded-lg px-4 py-2.5">' +
                        '<div class="flex items-center gap-2">' +
                        '<div class="w-2 h-2 rounded-full bg-indigo-500"></div>' +
                        '<div>' +
                        '<p class="text-sm text-slate-700">' + escapeHtml(rec.filename || 'Import') + '</p>' +
                        '<p class="text-xs text-slate-400">' + escapeHtml(rec.created_at || '') + '</p>' +
                        '</div>' +
                        '</div>' +
                        '<span class="text-xs font-medium text-slate-600">' + escapeHtml(String(rec.success_count || 0)) + ' rows</span>' +
                        '</div>';
                });
                container.innerHTML = html;
            } else {
                container.innerHTML = '<div class="text-center py-6 text-slate-400 text-sm">No import history yet.</div>';
            }
        })
        .catch(function (err) {
            if (err && (err.aborted || err.stale)) return;
            if (mySeq !== importHistorySeq) return;
            container.innerHTML = '<div class="text-center py-6 text-slate-400 text-sm">No import history available.</div>';
        });
}
