// ฟังก์ชันพิมพ์เฉพาะแถวของตาราง
function printRow(rowId) {
    const printContents = document.getElementById(rowId).innerHTML;
    const originalContents = document.body.innerHTML;
    document.body.innerHTML = `<table>${printContents}</table>`;
    window.print();
    document.body.innerHTML = originalContents;
}

// ฟังก์ชันสำหรับสร้าง PDF (ตัวอย่างที่ใช้การดาวน์โหลดพื้นฐาน)
function downloadPDF(rowId) {
    const rowContents = document.getElementById(rowId).innerText;
    const blob = new Blob([rowContents], { type: 'application/pdf' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'Product_Out.pdf';
    a.click();
    URL.revokeObjectURL(url);
}

// ฟังก์ชันพิมพ์ใบส่งออกสินค้าสำหรับแถวที่เลือก
function printRow(rowId) {
    const row = document.getElementById(rowId);
    const productID = row.children[0].textContent;
    const customerName = row.children[1].textContent;
    const productName = row.children[2].textContent;
    const outerSize = row.children[3].textContent;
    const quantity = row.children[4].textContent;
    const exportDate = row.children[5].textContent;
    const storageArea = row.children[6].textContent;
    const shelfSpace = row.children[7].textContent;
    const userName = row.children[8].textContent;
    const notes = row.children[9].textContent;
    const status = row.children[10].textContent;

    const printContents = `
        <div style="width: 80%; margin: 0 auto; font-family: Arial, sans-serif; line-height: 1.6;">
            <div style="display: flex; align-items: center; margin-bottom: 20px;">
                <img src="path/to/logo.png" alt="Company Logo" style="width: 80px; height: auto; margin-right: 20px;">
                <div>
                    <h2 style="margin: 0; color: #333;">Product Export Document</h2>
                    <p style="font-size: 0.9em; color: gray;">Export Date: ${exportDate}</p>
                </div>
            </div>
            
            <hr style="border-top: 1px solid #ddd; margin: 20px 0;">

            <div>
                <h3 style="color: #333; border-bottom: 1px solid #ddd; padding-bottom: 5px;">Exporter Details</h3>
                <p><strong>Handled By:</strong> ${userName}</p>
                <p><strong>Status:</strong> ${status}</p>
            </div>

            <div>
                <h3 style="color: #333; border-bottom: 1px solid #ddd; padding-bottom: 5px;">Customer Details</h3>
                <p><strong>Customer Name:</strong> ${customerName}</p>
                <p><strong>Notes:</strong> ${notes}</p>
            </div>

            <div>
                <h3 style="color: #333; border-bottom: 1px solid #ddd; padding-bottom: 5px;">Product Details</h3>
                <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                    <thead>
                        <tr>
                            <th style="border: 1px solid #ddd; padding: 8px;">Product ID</th>
                            <th style="border: 1px solid #ddd; padding: 8px;">Product Name</th>
                            <th style="border: 1px solid #ddd; padding: 8px;">Outer Size (mm)</th>
                            <th style="border: 1px solid #ddd; padding: 8px;">Quantity</th>
                            <th style="border: 1px solid #ddd; padding: 8px;">Storage Area</th>
                            <th style="border: 1px solid #ddd; padding: 8px;">Shelf Space</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="border: 1px solid #ddd; padding: 8px;">${productID}</td>
                            <td style="border: 1px solid #ddd; padding: 8px;">${productName}</td>
                            <td style="border: 1px solid #ddd; padding: 8px;">${outerSize}</td>
                            <td style="border: 1px solid #ddd; padding: 8px;">${quantity}</td>
                            <td style="border: 1px solid #ddd; padding: 8px;">${storageArea}</td>
                            <td style="border: 1px solid #ddd; padding: 8px;">${shelfSpace}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <hr style="border-top: 1px solid #ddd; margin: 20px 0;">

            <div style="display: flex; justify-content: space-between; margin-top: 50px;">
                <div style="text-align: center;">
                    <p>______________________________</p>
                    <p>Sender's Signature</p>
                </div>
                <div style="text-align: center;">
                    <p>______________________________</p>
                    <p>Receiver's Signature</p>
                </div>
            </div>
        </div>
    `;

    const originalContents = document.body.innerHTML;
    document.body.innerHTML = printContents;
    window.print();
    document.body.innerHTML = originalContents;
}
