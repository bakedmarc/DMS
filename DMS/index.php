<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Management System</title>
    <!-- Include Viewer.js library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.10.0/viewer.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.10.0/viewer.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .logo {
            position: absolute;
            top: 50px;
            left: 50px;
            width: 100px;
            z-index: -1;
        }

        h1 {
            text-align: center;
        }

        .container {
            display: flex;
            width: 100%;
            margin-top: 20px;
        }

        .section {
            flex: 1;
            padding: 20px;
            margin: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        #documentList {
            margin-top: 20px;
        }

        .document-item {
            margin-bottom: 10px;
            cursor: pointer;
        }

        #historyLog {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
        }

        #historyLog th, #historyLog td {
            border: 1px solid #dddddd;
            padding: 8px;
            text-align: left;
        }

        #historyLog th {
            background-color: #f2f2f2;
        }

        #preview {
            width: 100%;
            height: 80vh;
            overflow: auto;
        }
    </style>
</head>
<body>
    <img src="logo1.jpg" alt="Logo for Document Management System" class="logo">
    <h1> Document Management System</h1>
    <div class="container">
        <div class="section">
            <h2>Upload Document</h2>
            <!-- Department selection dropdown -->
            <select id="departmentSelect" onchange="changeDepartment()">
                <option value="accounting">Accounting Department</option>
                <option value="mechanical">Mechanical Department</option>
                <option value="IT">IT Department</option>
                <option value="electrical">Electrical Department</option>
            </select>
            <br><br>
            <input type="file" id="fileInput">
            <button onclick="uploadFile()">Upload</button>
            
            <div id="documentList"></div>
            <table id="historyLog">
                <thead>
                    <tr>
                        <th>Document</th>
                        <th>Uploaded at</th>
                        <th>Size</th>
                        <th>Action</th> <!-- New column header for Action -->
                    </tr>
                </thead>
                <tbody id="historyLogBody"></tbody>
            </table>
        </div>
        <div class="section">
            <h2>Preview Document</h2>
            <!-- Container for displaying the document -->
            <div id="preview"></div>
        </div>
    </div>
    <script>
        // Array to store uploaded documents
        const uploadedDocuments = [];
    
        // Function to upload a file
        function uploadFile() {
            const fileInput = document.getElementById('fileInput');
            const files = fileInput.files;
            const department = document.getElementById('departmentSelect').value; // Get selected department
    
            if (files.length === 0) {
                alert('Please select a file.');
                return;
            }
    
            const file = files[0];
            const reader = new FileReader();
    
            reader.onload = function(e) {
                const fileName = file.name;
                const fileContent = e.target.result;
                const fileSize = file.size;
                const uploadTime = new Date().toLocaleString();
                
                // Store document information with department
                const documentInfo = {
                    fileName: fileName,
                    fileContent: fileContent,
                    fileSize: fileSize,
                    uploadTime: uploadTime,
                    department: department // Add department information
                };
    
                // Add document to uploadedDocuments array
                uploadedDocuments.push(documentInfo);
    
                // Add document to history log if it matches the current department
                if (department === documentInfo.department) {
                    addToHistoryLog(documentInfo);
                }
            };
    
            reader.readAsDataURL(file);
        }
    
        // Function to add entry to history log
        function addToHistoryLog(documentInfo) {
            const historyLogBody = document.getElementById('historyLogBody');
    
            const newRow = historyLogBody.insertRow();
    
            const cell1 = newRow.insertCell(0);
            cell1.textContent = documentInfo.fileName;
    
            const cell2 = newRow.insertCell(1);
            cell2.textContent = documentInfo.uploadTime;
    
            const cell3 = newRow.insertCell(2);
            // Convert file size to human-readable format (KB)
            const fileSizeInKB = (documentInfo.fileSize / 1024).toFixed(2); // Convert to KB
            cell3.textContent = fileSizeInKB + ' KB'; // Display in KB
    
            // Add delete button
            const cell4 = newRow.insertCell(3);
            const deleteButton = document.createElement('button');
            deleteButton.textContent = 'Delete';
            deleteButton.addEventListener('click', function(event) {
                event.stopPropagation(); // Stop event propagation
                deleteDocument(newRow, documentInfo);
            });
            cell4.appendChild(deleteButton);
    
            // Add click event listener to preview the file
            newRow.addEventListener('click', function() {
                previewDocument(documentInfo);
            });
        }
    
        // Function to delete document from history log
        function deleteDocument(row, documentInfo) {
            const index = uploadedDocuments.findIndex(doc => doc.fileName === documentInfo.fileName);
            if (index !== -1) {
                uploadedDocuments.splice(index, 1);
                row.remove();
            }
        }
    
        // Function to preview document based on its type
        function previewDocument(documentInfo) {
            const fileType = getFileType(documentInfo.fileName);
            const preview = document.getElementById('preview');
    
            // Display close button
            preview.innerHTML = '<button onclick="closePreview()">Close</button>';
    
            switch(fileType) {
                case 'image':
                    displayImage(documentInfo.fileContent, documentInfo.fileName, preview);
                    break;
                case 'pdf':
                    displayPdf(documentInfo.fileContent, preview);
                    break;
                case 'docx':
                case 'pptx':
                case 'xlsx':
                    displayViewerJsPreview(documentInfo.fileContent, preview);
                    break;
                default:
                    alert('Preview not available for this file type.');
            }
        }
    
        // Function to close the document preview
        function closePreview() {
            const preview = document.getElementById('preview');
            preview.innerHTML = ''; // Clear the content of the preview element
        }
    
        // Function to determine file type based on extension
        function getFileType(fileName) {
            const extension = fileName.split('.').pop().toLowerCase();
            if (['jpg', 'jpeg', 'png', 'gif', 'bmp'].includes(extension)) {
                return 'image';
            } else if (extension === 'pdf') {
                return 'pdf';
            } else if (['docx', 'pptx', 'xlsx'].includes(extension)) {
                return extension;
            } else {
                return 'other';
            }
        }
    
        // Function to display Viewer.js preview for docx, pptx, and xlsx files
        function displayViewerJsPreview(fileContent, preview) {
            // Load the document using Viewer.js
            const viewer = new Viewer(preview, {
                inline: true,
                viewed() {
                    // Adjust viewer size after the document is viewed
                    viewer.viewerElement.style.height = '80vh';
                }
            });
            // Load the document content
            viewer.load(fileContent);
        }
    
        // Function to display image in preview
        function displayImage(fileContent, fileName, preview) {
            const imgElement = document.createElement('img');
            imgElement.src = fileContent;
            imgElement.alt = fileName;
            imgElement.style.maxWidth = '100%';
            imgElement.style.maxHeight = '80vh';
            preview.appendChild(imgElement);
        }
    
        // Function to display PDF in preview
        function displayPdf(fileContent, preview) {
            const embedElement = document.createElement('embed');
            embedElement.src = fileContent;
            embedElement.type = 'application/pdf';
            embedElement.style.width = '100%';
            embedElement.style.height = '80vh';
            preview.appendChild(embedElement);
        }
    
        // Function to change department
        function changeDepartment() {
            const department = document.getElementById('departmentSelect').value;
            const historyLogBody = document.getElementById('historyLogBody');
            // Clear history log
            historyLogBody.innerHTML = '';
            // Display documents for the selected department
            uploadedDocuments.forEach(doc => {
                if (doc.department === department) {
                    addToHistoryLog(doc);
                }
            });
            // Clear preview
            const preview = document.getElementById('preview');
            preview.innerHTML = '';
        }
    </script>
    
</body>
</html>
