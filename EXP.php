<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User-Friendly Sidebar</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.10.0/viewer.min.css">

</head>
<body>

<section class="sidebar">
    <img src="logo1.jpg" alt="Logo" style="width: 100px; margin: 20px;">
    <ul>
        <li><a href="#" onclick="showContent('upload')"><i class="fas fa-file-upload"></i> Upload Files</a></li>
        <li><a href="#" onclick="showContent('private-folder')"><i class="fas fa-folder"></i> Private Folder</a></li>
        <li><a href="#" onclick="showContent('account')"><i class="fas fa-user"></i> Account</a></li>
        <li><a href="login.php" onclick="logOut()"><i class="fas fa-sign-out-alt"></i> Log Out</a></li>
    </ul>
</section>

<section id="upload" class="content active">
    <h2>Upload Files</h2>
    <select id="departmentSelect" onchange="changeDepartment()">
        <option value="accounting">Accounting Department</option>
        <option value="mechanical">Mechanical Department</option>
        <option value="IT">IT Department</option>
        <option value="electrical">Electrical Department</option>
    </select>
    <a href="#" onclick="showAddDepartmentModal()"><i class="fas fa-plus-circle"></i> Add Department</a>
    <br><br>
    <div id="addDepartmentModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Add Department</h2>
            <form id="addDepartmentForm">
                <label for="departmentName">Department Name:</label>
                <input type="text" id="departmentName" name="departmentName" required>
                <br><br>
                <button type="submit">Submit</button>
            </form>
        </div>
    </div>
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
    <div class="section">
        <h2>Preview Document</h2>
        <!-- Container for displaying the document -->
        <div id="preview"></div>
    </div>
</section>

<section id="private-folder" class="content">
    <h2>Private Folder</h2>
    <p>This is the page where you can access your private folder.</p>
</section>

<section id="account" class="content">
    <h2>Account</h2>
    <p>This is the page where you can manage your account settings.</p>
</section>

<script src="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.10.0/viewer.min.js"></script>
<script>
// Array to store uploaded documents
let uploadedDocuments = [];

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

        // Add document to browser storage
        saveToStorage(uploadedDocuments);

        // Add document to history log if it matches the current department
        if (department === documentInfo.department) {
            addToHistoryLog(documentInfo);
        }
    };

    reader.readAsDataURL(file);
}

// Function to save uploaded documents to browser storage
function saveToStorage(documents) {
    localStorage.setItem('uploadedDocuments', JSON.stringify(documents));
}

// Function to load uploaded documents from browser storage
function loadFromStorage() {
    const storedDocuments = localStorage.getItem('uploadedDocuments');
    if (storedDocuments) {
        uploadedDocuments = JSON.parse(storedDocuments);
    }
}

// Function to add entry to history log
function addToHistoryLog(documentInfo) {
    const department = document.getElementById('departmentSelect').value;
    const historyLogBody = document.getElementById('historyLogBody');

    // Check if the document belongs to the current department
    if (documentInfo.department === department) {
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
}

// Function to delete document from history log and browser storage
function deleteDocument(row, documentInfo) {
    const index = uploadedDocuments.findIndex(doc => doc.fileName === documentInfo.fileName);
    if (index !== -1) {
        uploadedDocuments.splice(index, 1);
        saveToStorage(uploadedDocuments); // Update browser storage
        row.remove();
    }
}

// Function to be called when the page loads
window.onload = function() {
    loadFromStorage(); // Load uploaded documents from browser storage
    // Populate history log with uploaded documents
    uploadedDocuments.forEach(doc => {
        addToHistoryLog(doc);
    });
};
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
    preview.innerHTML += `<img src="${fileContent}" alt="${fileName}" style="max-width: 100%; max-height: 80vh;">`;
}

// Function to display PDF in preview
function displayPdf(fileContent, preview) {
    preview.innerHTML += `<embed src="${fileContent}" type="application/pdf" style="width: 100%; height: 80vh;">`;
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

// Function to show content based on navigation
function showContent(id) {
    // Hide all content sections
    var contentSections = document.querySelectorAll('.content');
    contentSections.forEach(function(section) {
        section.classList.remove('active');
    });

    // Show the selected content section
    var selectedSection = document.getElementById(id);
    selectedSection.classList.add('active');
}


// Function to show add department modal
function showAddDepartmentModal() {
    const modal = document.getElementById('addDepartmentModal');
    modal.style.display = 'block';
}

// Function to close the modal
function closeModal() {
    const modal = document.getElementById('addDepartmentModal');
    modal.style.display = 'none';
}

// Function to handle form submission for adding department
document.getElementById('addDepartmentForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent default form submission

    const departmentName = document.getElementById('departmentName').value.trim();
    
    if (departmentName === '') {
        alert('Please enter a department name.');
        return;
    }

    // Add department as an option to the dropdown
    const departmentSelect = document.getElementById('departmentSelect');
    const option = document.createElement('option');
    option.value = departmentName.toLowerCase().replace(/\s+/g, '-'); // Convert to lowercase and replace spaces with dashes
    option.textContent = departmentName;
    departmentSelect.appendChild(option);

    // Close the modal
    closeModal();
});

// Function to handle log out
function logOut() {
  
    
    // Redirect to a log out page or perform any other log out actions
    // For demonstration purposes, let's redirect to a hypothetical log out page
    window.location.href = 'login.php'; // Change 'logout.php' to the actual URL of your log out page
}

</script>

</body>
</html>