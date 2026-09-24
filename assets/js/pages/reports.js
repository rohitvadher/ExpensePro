var reportData = null;
var reportAbort = null;
var reportSeq = 0;

document.addEventListener('DOMContentLoaded', function () {
    if (window.ExpensePro && ExpensePro.utils && ExpensePro.utils.datePickers) {
        ExpensePro.utils.datePickers.init(document);
    }
    if (window.ExpensePro && ExpensePro.utils && ExpensePro.utils.date && ExpensePro.utils.date.ensure) {
        ExpensePro.utils.date.ensure().catch(function () {});
    }
    loadReport();
});

function formatLocalDate(date) {
    var y = date.getFullYear();
    var m = String(date.getMonth() + 1).padStart(2, '0');
    var d = String(date.getDate()).padStart(2, '0');
    return y + '-' + m + '-' + d;
}

function shiftLocalDate(date, days) {
    var d = new Date(date.getFullYear(), date.getMonth(), date.getDate());
    d.setDate(d.getDate() + days);
    return d;
}

function setQuickRange(period) {
    var from = document.getElementById('report-date-from');
    var to = document.getElementById('report-date-to');
    var today = new Date();
    var endDate = formatLocalDate(today);
    var startDate;

    if (period === 'week') {
        startDate = formatLocalDate(shiftLocalDate(today, -7));
    } else if (period === 'month') {
        startDate = formatLocalDate(shiftLocalDate(today, -30));
    } else if (period === 'quarter') {
        startDate = formatLocalDate(shiftLocalDate(today, -90));
    } else if (period === 'year') {
        startDate = formatLocalDate(shiftLocalDate(today, -365));
    }

    from.value = startDate;
    to.value = endDate;
    if (window.ExpensePro && ExpensePro.utils && ExpensePro.utils.datePickers) {
        ExpensePro.utils.datePickers.sync(from);
        ExpensePro.utils.datePickers.sync(to);
    }
    loadReport();
}

function loadReport() {
    var dateFrom = document.getElementById('report-date-from').value;
    var dateTo = document.getElementById('report-date-to').value;

    if (!dateFrom || !dateTo) {
        showToast('Please select both dates.', 'warning');
        return;
    }

    if (dateFrom > dateTo) {
        showToast('Start date must be before end date.', 'warning');
        return;
    }

    var url = BASE_URL + 'api/reports.php?date_from=' + encodeURIComponent(dateFrom) + '&date_to=' + encodeURIComponent(dateTo);

    if (reportAbort) {
        try { reportAbort.abort(); } catch (e) {}
    }
    reportAbort = ('AbortController' in window) ? new AbortController() : null;
    var signal = reportAbort ? reportAbort.signal : undefined;
    var mySeq = ++reportSeq;

    ajaxRequest('GET', url, null, { signal: signal }).then(function (response) {
        if (mySeq !== reportSeq) return;
        if (response.success && response.data) {
            reportData = response.data;
            renderReport(response.data);
        } else {
            showToast((response && response.message) || 'Failed to load report data.', 'error');
        }
    }).catch(function (error) {
        if (error && (error.aborted || error.stale)) return;
        if (mySeq !== reportSeq) return;
        var detail = (window.ExpensePro && window.ExpensePro.errors)
            ? window.ExpensePro.errors.handleApiError(error, { toast: false })
            : { message: (error && error.message) || 'Failed to load report.' };
        showToast(detail.message || 'Failed to load report.', 'error');
    });
}

function renderReport(data) {
    var s = data.summary;

    document.getElementById('rpt-income').textContent = formatCurrency(s.total_income);
    document.getElementById('rpt-expense').textContent = formatCurrency(s.total_expense);

    var balanceEl = document.getElementById('rpt-balance');
    balanceEl.textContent = formatCurrency(s.balance);
    balanceEl.className = 'text-lg font-bold ' + (s.balance >= 0 ? 'text-emerald-600' : 'text-rose-600');

    document.getElementById('rpt-count').textContent = String(s.transaction_count);

    document.getElementById('report-summary-cards').classList.remove('hidden');
    document.getElementById('report-empty').classList.add('hidden');
    document.getElementById('report-charts-section').classList.remove('hidden');
    document.getElementById('report-export-section').classList.remove('hidden');

    renderCategoryChart(data.categories || []);
    renderTrendChart(data.trend || { labels: [], income: [], expense: [], balance: [] });
    renderCategoryComparisonChart(data.category_comparison || { months: [], series: [] });
}

function renderCategoryChart(categories) {
    var container = document.getElementById('rpt-category-chart');
    if (!container) return;

    if (!categories || categories.length === 0) {
        container.innerHTML = '<div class="flex items-center justify-center h-[280px] text-slate-400 text-sm">No expense data for this period.</div>';
        return;
    }

    var labels = [];
    var series = [];
    var colors = [];
    categories.forEach(function (cat) {
        labels.push(escapeHtml(cat.name));
        series.push(cat.total);
        colors.push(safeCssColor(cat.color));
    });

    var options = {
        chart: { type: 'donut', height: 280 },
        labels: labels,
        series: series,
        colors: colors,
        legend: { position: 'bottom', fontSize: '12px', labels: { colors: '#64748b' } },
        plotOptions: { pie: { donut: { size: '65%' } } },
        dataLabels: { enabled: false },
        stroke: { width: 0 },
        tooltip: {
            y: { formatter: function (val) { return formatCurrency(val); } }
        }
    };

    mountChart(container.id, options);
}

function renderTrendChart(trend) {
    var container = document.getElementById('rpt-trend-chart');
    if (!container) return;

    if (!trend.labels || trend.labels.length === 0) {
        container.innerHTML = '<div class="flex items-center justify-center h-[280px] text-slate-400 text-sm">No trend data for this period.</div>';
        return;
    }

    var options = {
        chart: { type: 'area', height: 300, toolbar: { show: false }, sparkline: { enabled: false } },
        series: [
            { name: 'Income', data: trend.income },
            { name: 'Expense', data: trend.expense }
        ],
        colors: ['#22c55e', '#f43f5e'],
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.35,
                opacityTo: 0.05,
                stops: [0, 90, 100]
            }
        },
        stroke: { width: 2.5, curve: 'smooth' },
        plotOptions: {
            bar: { horizontal: false, columnWidth: '55%', borderRadius: 6 }
        },
        xaxis: {
            categories: trend.labels,
            labels: { style: { fontSize: '11px', colors: '#94a3b8' }, rotate: -45, rotateAlways: trend.labels.length > 6 },
            axisBorder: { show: false },
            axisTicks: { show: false }
        },
        yaxis: {
            labels: {
                style: { fontSize: '11px', colors: '#94a3b8' },
                formatter: function (v) { return '\u20B9' + (v >= 1000 ? (v / 1000).toFixed(0) + 'k' : v.toFixed(0)); }
            }
        },
        legend: { position: 'top', fontSize: '12px', labels: { colors: '#64748b', useSeriesColors: false }, markers: { width: 10, height: 10, radius: 5 } },
        grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
        tooltip: { shared: true, intersect: false, y: { formatter: function (val) { return formatCurrency(val); } } },
        dataLabels: { enabled: false },
        markers: { size: 4, strokeWidth: 0, hover: { size: 6 } }
    };

    mountChart(container.id, options);
}

function renderCategoryComparisonChart(cmp) {
    var container = document.getElementById('rpt-category-comparison-chart');
    if (!container) return;

    if (!cmp || !cmp.months || cmp.months.length === 0 || !cmp.series || cmp.series.length === 0) {
        container.innerHTML = '<div class="flex items-center justify-center h-[280px] text-slate-400 text-sm">No category comparison data for this period.</div>';
        return;
    }

    var options = {
        chart: {
            type: 'bar',
            height: 320,
            stacked: true,
            toolbar: { show: false }
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '45%',
                borderRadius: 4
            }
        },
        series: cmp.series,
        xaxis: {
            categories: cmp.months,
            labels: { style: { fontSize: '11px', colors: '#94a3b8' } },
            axisBorder: { show: false },
            axisTicks: { show: false }
        },
        yaxis: {
            labels: {
                style: { fontSize: '11px', colors: '#94a3b8' },
                formatter: function (v) { return '\u20B9' + (v >= 1000 ? (v / 1000).toFixed(0) + 'k' : v.toFixed(0)); }
            }
        },
        legend: {
            position: 'bottom',
            fontSize: '12px',
            labels: { colors: '#64748b' }
        },
        grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
        tooltip: {
            y: { formatter: function (val) { return formatCurrency(val); } }
        },
        dataLabels: { enabled: false }
    };

    mountChart(container.id, options);
}

function exportCSV() {
    if (!reportData || !reportData.summary) {
        showToast('Generate a report first.', 'warning');
        return;
    }

    var dateFrom = reportData.summary.date_from;
    var dateTo = reportData.summary.date_to;
    var url = BASE_URL + 'api/export.php?date_from=' + encodeURIComponent(dateFrom) + '&date_to=' + encodeURIComponent(dateTo);

    var link = document.createElement('a');
    link.href = url;
    link.download = 'expensepro_report_' + dateFrom + '_to_' + dateTo + '.csv';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    showToast('CSV downloaded!', 'success');
}

function exportPDF() {
    if (!reportData || !reportData.summary) {
        showToast('Generate a report first.', 'warning');
        return;
    }

    showToast('Generating PDF...', 'info', 3000);

    var s = reportData.summary;
    var txUrl = BASE_URL + 'api/reports.php?date_from=' + encodeURIComponent(s.date_from) + '&date_to=' + encodeURIComponent(s.date_to) + '&section=transactions';

    ajaxRequest('GET', txUrl).then(function (resp) {
        var transactions = (resp.success && resp.data && resp.data.transactions) ? resp.data.transactions : [];
        renderPDFDocument(s, reportData.categories || [], transactions, transactions.length >= 200);
    }).catch(function (error) {
        showToast((error && error.message) || 'Could not load report transactions. PDF export aborted.', 'error');
    });
}

function renderPDFDocument(s, cats, transactions, truncated) {
    if (typeof PDFEngine === 'undefined') {
        showToast('PDF engine is still loading. Please try again in a moment.', 'error');
        return;
    }
    var countNote = transactions.length > 0
        ? transactions.length + ' transaction(s) shown' + (truncated ? ' (list truncated by the server)' : '')
        : '';
    PDFEngine.renderReport(
        PDFEngine.wrap(
            PDFEngine.docHeader('Financial Report', s.date_from + ' — ' + s.date_to) +
            PDFEngine.summaryGrid(s) +
            '<div class="pdf-section">' +
            PDFEngine.sectionTitle('Expense by Category') +
            PDFEngine.categoryTable(cats) +
            '</div>'
        ),
        transactions,
        PDFEngine.filename('Report', s.date_from, s.date_to),
        countNote
    );
}
