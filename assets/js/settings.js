/**
 * Settings JavaScript file for Bookstore POS
 * Handles settings and user management functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize settings functionality
    initializeSettings();
});

/**
 * Initialize settings functionality
 */
function initializeSettings() {
    // Initialize user management
    initializeUserManagement();
    
    // Initialize system settings
    initializeSystemSettings();
    
    // Initialize database backup/restore
    initializeDatabaseBackup();
    
    // Add settings page styles
    addSettingsStyles();
}

/**
 * Initialize user management
 */
function initializeUserManagement() {
    // Add user button
    const addUserBtn = document.getElementById('add-user-btn');
    if (addUserBtn) {
        addUserBtn.addEventListener('click', openAddUserModal);
    }
    
    // Edit user buttons
    const editUserBtns = document.querySelectorAll('.edit-user-btn');
    editUserBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const userId = this.getAttribute('data-id');
            openEditUserModal(userId);
        });
    });
}

/**
 * Initialize system settings
 */
function initializeSystemSettings() {
    // Tax settings functionality has been removed
}

/**
 * Initialize database backup/restore
 */
function initializeDatabaseBackup() {
    // Backup button
    const backupBtn = document.getElementById('backup-btn');
    if (backupBtn) {
        backupBtn.addEventListener('click', backupDatabase);
    }
    
    // Restore button
    const restoreBtn = document.getElementById('restore-btn');
    if (restoreBtn) {
        restoreBtn.addEventListener('click', restoreDatabase);
    }
}

/**
 * Open add user modal
 */
function openAddUserModal() {
    const template = document.getElementById('user-template');
    if (!template) return;
    
    openModal('Add New User', template.content.cloneNode(true).querySelector('.user-form').outerHTML, addUser);
}

/**
 * Open edit user modal
 * @param {string} userId User ID
 */
function openEditUserModal(userId) {
    const template = document.getElementById('user-template');
    if (!template) return;
    
    // Show loading notification
    showNotification('Loading user data...', 'info');
    
    // In a real application, you'd fetch user data from an API
    // For now, we'll get it from the table row
    try {
        const userRow = document.querySelector(`.edit-user-btn[data-id="${userId}"]`).closest('tr');
        if (!userRow) {
            showNotification('User data not found', 'error');
            return;
        }
        
        const name = userRow.cells[0].textContent.trim();
        const username = userRow.cells[1].textContent.trim();
        const role = userRow.cells[2].textContent.toLowerCase().trim();
        
        openModal('Edit User', template.content.cloneNode(true).querySelector('.user-form').outerHTML, function() {
            updateUser(userId);
        });
        
        // Fill in form with user data
        const nameField = document.getElementById('user-name');
        const usernameField = document.getElementById('user-username');
        const roleField = document.getElementById('user-role');
        
        if (nameField) nameField.value = name;
        if (usernameField) usernameField.value = username;
        if (roleField) roleField.value = role === 'admin' ? 'admin' : 'staff';
        
        // Hide password fields for edit mode
        const passwordFields = document.querySelectorAll('.password-field');
        passwordFields.forEach(field => {
            field.style.display = 'none';
        });
        
        // Add a note about password
        const formNote = document.querySelector('.form-note');
        if (formNote) {
            formNote.innerHTML = '* Required fields<br>Note: Password fields are hidden when editing a user.';
        }
    } catch (error) {
        console.error('Error retrieving user data:', error);
        showNotification('An error occurred while loading user data', 'error');
    }
}

/**
 * Add user
 */
function addUser() {
    const name = document.getElementById('user-name').value;
    const username = document.getElementById('user-username').value;
    const password = document.getElementById('user-password').value;
    const confirmPassword = document.getElementById('user-confirm-password').value;
    const role = document.getElementById('user-role').value;
    
    // Validate inputs
    if (!name || !username || !password || !confirmPassword) {
        showNotification('Please fill in all required fields', 'warning');
        return;
    }
    
    if (password !== confirmPassword) {
        showNotification('Passwords do not match', 'warning');
        return;
    }
    
    // Prepare user data
    const userData = {
        name: name,
        username: username,
        password: password,
        role: role
    };
      // Show loading indicator
    showNotification('Creating user...', 'info');
    
    // Make API call to create the user
    fetch('api/user/create.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(userData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeModal();
            showNotification('User created successfully', 'success');
            
            // Reload the page to show the new user
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showNotification(data.message || 'Failed to create user', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while creating the user', 'error');
    });
}

/**
 * Update user
 * @param {string} userId User ID
 */
function updateUser(userId) {
    try {
        const name = document.getElementById('user-name').value.trim();
        const username = document.getElementById('user-username').value.trim();
        const role = document.getElementById('user-role').value;
        
        // Validate inputs
        if (!name || !username) {
            showNotification('Please fill in all required fields', 'warning');
            return;
        }
        
        // Prepare user data
        const userData = {
            user_id: userId,
            name: name,
            username: username,
            role: role
        };
        
        // Show loading indicator
        showNotification('Updating user...', 'info');
        
        // Make the actual API call to update the user
        fetch('api/user/update.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(userData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                try {
                    // Find the user row in the table to update UI
                    const userRow = document.querySelector(`.edit-user-btn[data-id="${userId}"]`).closest('tr');
                    
                    if (userRow) {
                        // Update the row data
                        userRow.cells[0].textContent = name;
                        userRow.cells[1].textContent = username;
                        userRow.cells[2].textContent = role.charAt(0).toUpperCase() + role.slice(1);
                    }
                } catch (error) {
                    console.error('Error updating UI:', error);
                }
                
                closeModal();
                showNotification('User updated successfully', 'success');
            } else {
                showNotification(data.message || 'Failed to update user', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred while updating the user', 'error');
        });
    } catch (error) {
        console.error('Error in update user function:', error);
        showNotification('An error occurred while updating the user', 'error');
    }
}

/**
 * Save tax rate - This function is no longer needed
 */
function saveTaxRate() {
    // This function is no longer used as tax functionality has been removed
}

/**
 * Backup database
 */
function backupDatabase() {
    // Show loading indicator
    showNotification('Backing up database...', 'info');
    
    // In a real application, this would make an API call to backup the database
    // For this demo, we'll simulate success after a delay
    setTimeout(() => {
        showNotification('Database backup created successfully', 'success');
        
        // Simulate download
        const a = document.createElement('a');
        a.style.display = 'none';
        a.href = 'data:application/sql;charset=utf-8,' + encodeURIComponent('-- Simulated database backup');
        a.download = 'bookstore_pos_backup_' + new Date().toISOString().slice(0, 10) + '.sql';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    }, 2000);
}

/**
 * Restore database
 */
function restoreDatabase() {
    // Create a file input element
    const fileInput = document.createElement('input');
    fileInput.type = 'file';
    fileInput.accept = '.sql';
    fileInput.style.display = 'none';
    document.body.appendChild(fileInput);
    
    // Show file dialog
    fileInput.click();
    
    // Handle file selection
    fileInput.addEventListener('change', function() {
        if (fileInput.files.length > 0) {
            const file = fileInput.files[0];
            
            // Show loading indicator
            showNotification('Restoring database from ' + file.name + '...', 'info');
            
            // In a real application, this would make an API call to restore the database
            // For this demo, we'll simulate success after a delay
            setTimeout(() => {
                showNotification('Database restored successfully', 'success');
            }, 2000);
        }
        
        // Clean up
        document.body.removeChild(fileInput);
    });
}

/**
 * Add settings page styles
 */
function addSettingsStyles() {
    const settingsStyles = document.createElement('style');
    settingsStyles.textContent = `
        .settings-section {
            margin-bottom: 2rem;
        }
        
        .settings-section h3 {
            font-size: 1.1rem;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .profile-info, .app-info {
            display: grid;
            gap: 0.8rem;
        }
        
        .profile-field, .info-field {
            display: flex;
        }
        
        .field-label {
            width: 100px;
            font-weight: 500;
        }
        
        .password-form, .tax-form {
            max-width: 400px;
        }
        
        .alert {
            padding: 0.8rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }
        
        .alert-success {
            background-color: #e8f5e9;
            color: #4caf50;
            border: 1px solid #c8e6c9;
        }
        
        .alert-danger {
            background-color: #ffebee;
            color: #f44336;
            border: 1px solid #ffcdd2;
        }
        
        .users-table-wrapper {
            overflow-x: auto;
        }
        
        .users-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .users-table th, .users-table td {
            padding: 0.8rem;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        
        .users-table th {
            font-weight: 500;
            border-bottom: 2px solid var(--border-color);
        }
        
        .backup-actions {
            display: flex;
            gap: 1rem;
        }
        
        .about-content {
            line-height: 1.6;
        }
        
        .about-content p {
            margin-bottom: 1rem;
        }
    `;
    
    document.head.appendChild(settingsStyles);
}