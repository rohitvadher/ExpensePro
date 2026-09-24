(function () {
    'use strict';

    function round2(value) {
        return Math.round((Number(value) + Number.EPSILON) * 100) / 100;
    }

    function toPaise(value) {
        return Math.round(Number(value) * 100);
    }

    function calcTotals(income, expense) {
        income = round2(income || 0);
        expense = round2(expense || 0);
        return { income: income, expense: expense, balance: round2(income - expense) };
    }

    function calcPercentage(part, total) {
        part = Number(part) || 0;
        total = Number(total) || 0;
        if (total <= 0) return 0;
        return Math.round(((part / total) * 100) * 10) / 10;
    }

    function calcSavingsRate(balance, income) {
        balance = Number(balance) || 0;
        income = Number(income) || 0;
        if (income <= 0) return 0;
        return Math.round(((balance / income) * 100) * 10) / 10;
    }

    function calcBudgetStatus(spent, limit) {
        spent = round2(Math.max(0, Number(spent) || 0));
        limit = round2(Math.max(0, Number(limit) || 0));
        return {
            spent: spent,
            remaining: round2(Math.max(0, limit - spent)),
            percentage: calcPercentage(spent, limit),
            is_over: spent > limit
        };
    }

    function formatINR(amount) {
        if (amount === null || amount === undefined || isNaN(Number(amount))) return '₹0.00';
        var num = Number(amount);
        var isNeg = num < 0;
        num = Math.abs(num);
        var parts = num.toFixed(2).split('.');
        var intPart = parts[0];
        var decPart = parts[1];
        var lastThree = intPart.substring(intPart.length - 3);
        var otherNumbers = intPart.substring(0, intPart.length - 3);
        if (otherNumbers !== '') {
            lastThree = ',' + lastThree;
        }
        var formatted = otherNumbers.replace(/\B(?=(\d{2})+(?!\d))/g, ',') + lastThree;
        return (isNeg ? '-₹' : '₹') + formatted + '.' + decPart;
    }

    window.ExpensePro = window.ExpensePro || {};
    window.ExpensePro.finance = {
        round2: round2,
        toPaise: toPaise,
        calcTotals: calcTotals,
        calcPercentage: calcPercentage,
        calcSavingsRate: calcSavingsRate,
        calcBudgetStatus: calcBudgetStatus,
        formatINR: formatINR
    };
})();
