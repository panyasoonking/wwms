// ฟังก์ชันดึงข้อมูลล่าสุดจากฐานข้อมูล
function fetchLatestData() {
    fetch('fetch_latest_data.php')
        .then(response => response.json())
        .then(data => {
            // ค้นหาตารางและเคลียร์ข้อมูลเก่าออก
            const tableBody = document.querySelector('#productInTable tbody');
            tableBody.innerHTML = '';

            // เพิ่มข้อมูลใหม่จากฐานข้อมูล
            data.forEach(row => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${row.product_id}</td>
                    <td>${row.customer_name}</td>
                    <td>${row.product_name}</td>
                    <td>${row.outer_size}</td>
                    <td>${row.quantity}</td>
                    <td>${row.checkin_date}</td>
                    <td>${row.storage_area}</td>
                    <td>${row.shelf_space}</td>
                    <td>${row.user_name}</td>
                    <td>${row.detailed_notes}</td>
                    <td>${row.product_status}</td>
                `;
                tableBody.appendChild(tr);
            });
        })
        .catch(error => console.error('Error fetching data:', error));
}

// เรียก fetchLatestData ทุก ๆ 5 วินาที
setInterval(fetchLatestData, 5000);

// เรียกครั้งแรกเมื่อโหลดหน้า
window.onload = fetchLatestData;

/* ปุ่ม Print และ PDF */
.action-button {
    color: white;
    padding: 6px 8px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 0.85em;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 75px;
    transition: background-color 0.3s ease;
    margin: 2px auto;
    box-sizing: border-box;
}

.print-button {
    background-color: #007BFF;
}

.pdf-button {
    background-color: #FF5733;
}

.action-button i {
    margin-right: 5px;
    font-size: 1em;
}

.action-button:hover {
    opacity: 0.9;
}