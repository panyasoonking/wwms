// dashboard.js

// สร้างกราฟแท่งแสดงข้อมูลสินค้ารับเข้าและส่งออกแยกตามเดือน
const ctx = document.getElementById('productChart').getContext('2d');
const productChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: checkinMonths,
        datasets: [
            {
                label: 'Received Products',
                data: checkinTotals,
                backgroundColor: '#4CAF50',
                borderColor: '#388E3C',
                borderWidth: 1,
                borderRadius: 4,
            },
            {
                label: 'Exported Products',
                data: checkoutTotals,
                backgroundColor: '#FF5733',
                borderColor: '#E53935',
                borderWidth: 1,
                borderRadius: 4,
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true,
                labels: {
                    color: '#333',
                    font: {
                        size: 14,
                        weight: 'bold'
                    }
                }
            },
            tooltip: {
                backgroundColor: '#333',
                titleFont: { size: 14 },
                bodyFont: { size: 12 }
            }
        },
        scales: {
            x: {
                grid: {
                    display: false
                },
                ticks: {
                    color: '#666',
                    font: {
                        size: 14
                    }
                }
            },
            y: {
                beginAtZero: true,
                grid: {
                    color: '#ddd',
                    lineWidth: 1
                },
                ticks: {
                    color: '#666',
                    font: {
                        size: 14
                    }
                }
            }
        },
        layout: {
            padding: 20
        }
    }
});


// ฟังก์ชันแสดงตารางที่เลือก
function showTable(tableName) {
    fetch(`function/fetch_${tableName}.php`)
        .then(response => response.text())
        .then(data => document.getElementById('tableBody').innerHTML = data);
}

// ฟังก์ชันค้นหาในตาราง
function searchTable() {
    const input = document.getElementById('searchInput').value.toUpperCase();
    const rows = document.getElementById('tableBody').getElementsByTagName('tr');
    for (let i = 0; i < rows.length; i++) {
        const cells = rows[i].getElementsByTagName('td');
        let match = false;
        for (let j = 0; j < cells.length; j++) {
            if (cells[j].textContent.toUpperCase().includes(input)) {
                match = true;
                break;
            }
        }
        rows[i].style.display = match ? "" : "none";
    }
}

