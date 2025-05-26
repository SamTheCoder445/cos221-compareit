
let pricesChart, wishlistsChart;

function updateChart(chart, labels, data, labelText ="", borderColour = "blue"){
    chart.data.label = labels;
    chart.data.datasets = [{
        label: labelText,
        data: data,
        borderColor: borderColour,
        fill: false,
        tension: 0.3
    }];
    chart.update();
}


document.addEventListener('DOMContentLoaded', function(){
    
    const ctx = document.getElementById('priceChart').getContext('2d');
    pricesChart = new Chart(ctx, {
        type: 'line',
        data: {
        labels: ['May 5', 'May 10', 'May 15', 'May 20', 'May 25'],
        datasets: [{
            label: 'Average Price (per R1,000)',
            data: [4, 4.3, 4.2, 4.1, 4.2],
            borderColor: '#0d6efd',
            fill: false,
            tension: 0.3
        }]
        },
        options: {
        responsive: true,
        scales: {
            y: { beginAtZero: false }
        }
        }
    });

    const ctxWishlist = document.getElementById('wishlistChart').getContext('2d');
    wishlistsChart = new Chart(ctxWishlist, {
        type: 'line',
        data: {
        labels: ['May 5', 'May 10', 'May 15', 'May 20', 'May 25'],
        datasets: [{
            label: 'Average Saves (per 1,000)',
            data: [0.5, 0.3, 0.32, 0.45, 0.65],
            borderColor: '#0d6efd',
            fill: false,
            tension: 0.3
        }]
        },
        options: {
        responsive: true,
        scales: {
            y: { beginAtZero: false }
        }
        }
    });
;

})

export {updateChart, pricesChart, wishlistsChart};