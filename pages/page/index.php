<?php
include_once('../../header.php');

?>
<style>
.grid-container {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    padding: 10px;
}


.tool-item {
    background-color: #fff;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
}

.tool-item:hover {
    transform: scale(1.05);
}

h3 {
    margin: 0;
    font-size: 1.2em;
}

p {
    margin: 10px 0;
}

.buy-button {
    display: inline-block;
    background-color: #28a745;
    color: #fff;
    padding: 8px 12px;
    border: none;
    border-radius: 4px;
    text-decoration: none;
    cursor: pointer;
    text-align: center;
}


@media (max-width: 600px) {
    .grid-container {
        grid-template-columns: 1fr;
    }
}


@media (min-width: 601px) and (max-width: 1024px) {
    .grid-container {
        grid-template-columns: repeat(2, 1fr);
    }
}


@media (min-width: 1025px) {
    .grid-container {
        grid-template-columns: repeat(3, 1fr);
    }
}

a.buy-button {
    display: inline-block;
    background-color: #0c182f;
    color: #fff;
    text-decoration: none;
    padding: 8px 12px;
    border-radius: 4px;
    font-size: 14px;
    font-weight: bold;
    transition: background-color 0.3s ease;
}

a.buy-button {
    height: 38px !important;
    width: 100px !important;
    text-align: center !important;
}

a.buy-button:hover {
    background-color: #218838;
    cursor: pointer;
}

#pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
}

#pagination button {
    background-color: #0c182f;
    color: #fff;
    border: none;
    padding: 10px 20px;
    font-size: 16px;
    cursor: pointer;
}

#pagination button:hover {
    background-color: #3e8e41;
}

#pagination button:disabled {
    background-color: #ddd;
    color: #666;
    cursor: not-allowed;
}

#pagination span {
    font-size: 16px;
    margin: 0 10px;
}
</style>

<div class="main-content">
    <div id="leads" class="uuper">
        <h2>Leads Section</h2>

        <!-- Search Bar -->
        <div style="text-align: center; margin-bottom: 10px;">
            <h3 style="margin: 0; font-size: 18px; color: #333;">Search for pages</h3>
        </div>
        <div
            style="position: relative; width: 100%; max-width: 400px; margin: 0 auto; display: flex; align-items: center; justify-content: center;">
            <input type="text" id="searchBar" placeholder="Search pages..."
                style="width: 100%; padding: 12px 20px; border-radius: 25px; border: 1px solid #ccc; font-size: 16px; transition: all 0.3s ease-in-out;"
                onfocus="this.style.borderColor='#007bff';" onblur="this.style.borderColor='#ccc';" />
            <i class="search-icon fas fa-search"
                style="position: absolute; right: 15px; color: #aaa; font-size: 20px; cursor: pointer; transition: color 0.3s ease;"
                onmouseover="this.style.color='#007bff';" onmouseout="this.style.color='#aaa';">
            </i>
        </div>


        <?php if (empty($files['Leads'])): ?>
        <p>No files available in the Leads section.</p>
        <?php else: ?>
        <div class="grid-container" id="file-grid">
            <!-- Files will be inserted here -->
        </div>
        <!-- Pagination controls -->
        <div id="pagination"></div>
        <?php endif; ?>
    </div>
</div>



</div>
</div>
<div id="rules-popup" class="popup-modal" style="display: none;">
    <div class="popup-content" style="position: absolute;top: 50%;right: 20%;">
        <span class="close" onclick="closeRulesPopup()">
            <i class="fas fa-times"></i>
        </span>
        <p class="message"></p>
    </div>
</div>
<?php
include_once('../../footer.php');
?>
<script>
document.getElementById('searchBar').addEventListener('input', function() {
    let searchQuery = this.value.toLowerCase();
    fetchFiles(1, searchQuery); // Call fetchFiles with search query
});

// Fetch files with optional search query
function fetchFiles(currentPage = 1, searchQuery = '') {
    fetch(`get_files.php?section=Leads&page=${currentPage}&search=${searchQuery}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to fetch files');
            }
            return response.json();
        })
        .then(data => {
            const gridContainer = document.getElementById("file-grid");
            gridContainer.innerHTML = ''; // Clear existing content

            if (data.files && data.files.length > 0) {
                data.files.forEach(file => {
                    const price = parseFloat(file.price);
                    const formattedPrice = isNaN(price) ? 'N/A' : `$${price.toFixed(2)}`;
                    const isActive = <?= json_encode($_SESSION['active'] === 1); ?>;

                    const fileItem = `
                            <div class="tool-item">
                                <h3>${escapeHtml(file.name)}</h3>
                                <p>${escapeHtml(file.description).replace(/\n/g, '<br>')}</p>
                                <p>Price: ${formattedPrice}</p>
                                <a class="buy-button ${!isActive ? 'disabled' : ''}" href="buy_tool.php?tool_id=${file.id}&section=leads" 
                                    data-id="${file.id}" data-section="leads">
                                    <span class="price">${formattedPrice}</span>
                                    <span class="buy-now">Buy Now</span>
                                </a>
                            </div>
                        `;
                    gridContainer.innerHTML += fileItem;
                });
                createPagination(data.currentPage, data.totalPages);
            } else {
                gridContainer.innerHTML = '<p>No files found matching your search.</p>';
            }
        })
        .catch(error => {
            console.error('Error fetching files:', error);
            alert('Failed to load files. Please try again later.');
        });
}

function createPagination(currentPage, totalPages) {
    const paginationContainer = document.getElementById("pagination");
    paginationContainer.innerHTML = '';
    const prevButton = document.createElement("button");
    prevButton.textContent = "Previous";
    prevButton.disabled = currentPage <= 1;
    prevButton.addEventListener("click", () => {
        if (currentPage > 1) {
            fetchFiles(currentPage - 1);
        }
    });
    const nextButton = document.createElement("button");
    nextButton.textContent = "Next";
    nextButton.disabled = currentPage >= totalPages;
    nextButton.addEventListener("click", () => {
        if (currentPage < totalPages) {
            fetchFiles(currentPage + 1);
        }
    });
    const pageInfo = document.createElement("span");
    pageInfo.textContent = `Page ${currentPage} of ${totalPages}`;
    paginationContainer.appendChild(prevButton);
    paginationContainer.appendChild(pageInfo);
    paginationContainer.appendChild(nextButton);
}

function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/\"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

fetchFiles();

function showPopupMessage(message, type) {
    const popup = document.getElementById('rules-popup');
    const messageElement = popup.querySelector('.message');

    messageElement.textContent = message;


    popup.className = `popup-modal ${type}`;
    popup.style.display = 'block';
}


function closeRulesPopup() {
    const popup = document.getElementById('rules-popup');
    popup.style.display = 'none';
}


document.addEventListener("click", function(event) {
    if (event.target.closest(".buy-button")) {
        event.preventDefault(); // Prevent the default navigation

        const button = event.target.closest(".buy-button");
        const toolId = button.getAttribute("data-id");
        const section = button.getAttribute("data-section");

        // Use alertify for confirmation
        alertify.confirm(
            "Confirmation",
            "Are you sure you want to buy this item?",
            function() {
                // Send AJAX request if confirmed
                fetch("buy_tool.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded",
                        },
                        body: `tool_id=${toolId}&section=${section}`,
                    })
                    .then((response) => response.json()) // Parse the JSON response
                    .then((data) => {
                        console.log(data);
                        if (data.success) {
                            showPopupMessage(data.success, 'success');
                            setTimeout(() => window.location.reload(), 2000);
                        } else {
                            showPopupMessage(data.error, 'error');
                        }
                    })
                    .catch((error) => {
                        console.error("Error buying tool:", error);
                        alertify.error("An error occurred. Please try again later.");
                    });
            },
            function() {
                // Action if canceled
                alertify.error("Purchase cancelled.");
            }
        ).set("labels", {
            ok: "Confirm",
            cancel: "Cancel"
        });
    }
});
</script>

</body>

</html>