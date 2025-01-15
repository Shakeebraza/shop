<?php
include_once('../../header.php');
?>
<style>
.grid-container {
    display: grid;
    gap: 1rem;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    margin: 1rem 0;
}

.tool-item {
    border: 1px solid #ddd;
    border-radius: 5px;
    padding: 1rem;
    text-align: center;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
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
<!-- Main Content Area -->
<div class="main-content">

    <div id="my-orders" class="uuper">
        <h2>My Orders</h2>
        <div id="orders-container" class="grid-container">
            <p>Loading orders...</p>
        </div>
        <div id="orders-container"></div>
        <div id="pagination"></div>
    </div>
</div>
</div>
<?php
include_once('../../footer.php');
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    fetchOrders(1); // Fetch orders for the first page
});

function createPagination(currentPage, totalPages) {
    const paginationContainer = document.getElementById("pagination");
    paginationContainer.innerHTML = '';

    // Previous button
    const prevButton = document.createElement("button");
    prevButton.textContent = "Previous";
    prevButton.disabled = currentPage <= 1;
    prevButton.addEventListener("click", () => {
        if (currentPage > 1) {
            fetchOrders(currentPage - 1);
        }
    });
    paginationContainer.appendChild(prevButton);

    // Page links
    for (let i = 1; i <= totalPages; i++) {
        const pageLink = document.createElement("button");
        pageLink.textContent = i;
        pageLink.className = i === currentPage ? "active" : "";
        pageLink.addEventListener("click", () => fetchOrders(i));
        paginationContainer.appendChild(pageLink);
    }

    // Next button
    const nextButton = document.createElement("button");
    nextButton.textContent = "Next";
    nextButton.disabled = currentPage >= totalPages;
    nextButton.addEventListener("click", () => {
        if (currentPage < totalPages) {
            fetchOrders(currentPage + 1);
        }
    });
    paginationContainer.appendChild(nextButton);
}

function fetchOrders(page = 1) {
    fetch(`get_orders.php?page=${page}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to fetch orders');
            }
            return response.json();
        })
        .then(data => {
            const ordersContainer = document.getElementById('orders-container');
            ordersContainer.innerHTML = ''; // Clear existing content

            // Render orders
            if (data.orders && data.orders.length > 0) {
                data.orders.forEach(order => {
                    const orderItem = `
                            <div class="tool-item">
                                <h3>${escapeHtml(order.name)}</h3>
                                <p>${escapeHtml(order.description).replace(/\n/g, '<br>')}</p>
                                <p>Price: $${parseFloat(order.price).toFixed(2)}</p>
                                <a href="download_tool.php?tool_id=${order.tool_id}" class="download-button">Download</a>
                                <a href="delete_order.php?tool_id=${order.tool_id}&section=my-orders" 
                                   onclick="return confirm('Are you sure you want to delete this item?');" 
                                   class="delete-button">Delete</a>
                            </div>
                        `;
                    ordersContainer.innerHTML += orderItem;
                });
            } else {
                ordersContainer.innerHTML = '<p>You haven\'t made any purchases yet.</p>';
            }

            // Create pagination
            createPagination(page, data.totalPages);
        })
        .catch(error => {
            console.error('Error fetching orders:', error);
            const ordersContainer = document.getElementById('orders-container');
            ordersContainer.innerHTML = '<p>Failed to load orders. Please try again later.</p>';
        });
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}
</script>