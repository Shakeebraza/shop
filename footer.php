<div id="rules-popup" class="popup-modal" style="display: none;">
    <div class="popup-content" style="position: absolute;top: 50%;right: 20%;">
        <span class="close" onclick="closeRulesPopup()">
            <i class="fas fa-times"></i>
        </span>
        <p class="message"></p>  <!-- This will be dynamically replaced by success/error message -->
    </div>
</div>

<footer class="footer">
    <div class="footer-content">
        &copy; CardVault 2025
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript" charset="utf-8" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/qrious/dist/qrious.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/default.min.css"/>
<script>
$(document).ready(function() {
    $('#userDropdownToggle').click(function() {
        $('#userDropdownMenu').toggle('fast');
    });
});
function showPopupMessage(type, message) {
    const popup = document.getElementById('rules-popup');
    const popupContent = popup.querySelector('.popup-content');


    popupContent.innerHTML = `
        <span class="close" onclick="closeRulesPopup()">
            <i class="fas fa-times"></i>
        </span>
        <p class="${type === 'success' ? 'message-success' : 'message-error'}">${message}</p>
    `;

    popup.style.display = 'block';
}

document.addEventListener('DOMContentLoaded', function() {
    particlesJS('particles-js', {
        "particles": {
            "number": {
                "value": 80,
                "density": { "enable": true, "value_area": 800 }
            },
            "color": { "value": "#8b84c6" },
            "shape": { "type": "circle" },
            "opacity": { "value": 0.7 },
            "size": { "value": 4, "random": true },
            "line_linked": {
                "enable": true,
                "distance": 150,
                "color": "#8b84c6",
                "opacity": 0.4,
                "width": 1
            },
            "move": { "enable": true, "speed": 2 }
        },
        "interactivity": {
            "events": {
                "onhover": { "enable": true, "mode": "repulse" },
                "onclick": { "enable": true, "mode": "push" }
            }
        },
        "retina_detect": true
    });
});


   
document.addEventListener('DOMContentLoaded', function () {
    fetch('<?= $urlval ?>getcart.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
              
                updateCartSidebar(data.cartItems, data.dumpsItems, data.total);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
});

function updateCartSidebar(cartItems, dumpsItems, total) {
    const cartCardsContainer = document.getElementById('cartCards');
    const cartDumpsContainer = document.getElementById('cartDumps');
    const cartTotal = document.getElementById('cartTotal');

    // Reset the containers
    cartCardsContainer.innerHTML = '';
    cartDumpsContainer.innerHTML = '';

    // Ensure that cartItems is an array before processing
    if (Array.isArray(cartItems)) {
        cartItems.forEach(item => {
            const cartItem = document.createElement('div');
            cartItem.className = 'cart-item';
            cartItem.innerHTML = `
                <img src="${item.image}" alt="Item Image" style="width: 50px; height: 50px; object-fit: cover;">
                <div class="cart-item-details">
                    <h4>${item.bin}</h4>
                    <p>$${item.price}</p>
                </div>
                <span style="cursor: pointer;" onclick="removeFromCart(${item.id})">&times;</span>
            `;
            cartCardsContainer.appendChild(cartItem);
        });
    }

    // Ensure that dumpsItems is an array before processing
    if (Array.isArray(dumpsItems)) {
        dumpsItems.forEach(item => {
            const cartItem = document.createElement('div');
            cartItem.className = 'cart-item';
            cartItem.innerHTML = `
                <img src="${item.image}" alt="Item Image" style="width: 50px; height: 50px; object-fit: cover;">
                <div class="cart-item-details">
                    <h4>${item.bin}</h4>
                    <p>$${item.price}</p>
                </div>
                <span style="cursor: pointer;" onclick="removeFromdumps(${item.id})">&times;</span>
            `;
            cartDumpsContainer.appendChild(cartItem);
        });
    }

 
    const formattedTotal = isNaN(total) ? 0 : total;
    cartTotal.textContent = formattedTotal.toFixed(2);
}



       
        function removeFromCart(cardId) {
            fetch('<?= $urlval?>ajax/removefromcart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ cardId: cardId }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                 
                    updateCartSidebar(data.cartItems,data.dumpsItems, data.total);
                    updateCartCount()
                } else {
                    alert(data.message); 
                }
            })
            .catch(error => console.error('Error:', error));
        }
       
        function removeFromdumps(cardId) {
            fetch('<?= $urlval?>ajax/removefromdumps.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ cardId: cardId }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                
                    updateCartSidebar(data.cartItems,data.dumpsItems, data.total);
                    updateCartCount()
                } else {
                    alert(data.message); 
                }
            })
            .catch(error => console.error('Error:', error));
        }

    function updateCartCount() {
        fetch('<?= $urlval ?>ajax/cart-count.php')
            .then(response => response.json())
            .then(data => {
                document.getElementById('cartBadge').textContent = data.count;
            })
            .catch(error => console.error('Error fetching cart count:', error));
    }
    function getCartItems() {
    return fetch('<?= $urlval?>ajax/getCartItems.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                return data.cartItems || []; 
            } else {
                return []; 
            }
        })
        .catch(error => {
            console.error('Error:', error);
            return []; 
        });
}

async function proceedToCheckout() {
    alertify.confirm(
        'Confirm Purchase',
        `Are you sure you want to buy all the cards and dumps?`,
        async function () {
            try {
                // Fetch cart items (both cards and dumps)
                const cartItems = await getCartItems();

                if (cartItems.length === 0) {
                    alert("Your cart is empty!");
                    return;
                }

                // Prepare payload
                const payload = {
                    cartItems: cartItems.map(item => ({
                        id: item.id,
                        quantity: item.quantity,
                        type: item.type, // Optional, distinguish between card and dump
                    })),
                };

                // Send payload to the server
                const response = await fetch('<?= $urlval?>ajax/checkout.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(payload),
                });

                const data = await response.json();

                if (data.success) {
                    showPopupMessage('success', data.message || 'Purchase successful.');
                    removeAllFromCart(); 
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    removeAllFromCart(); 
                    showPopupMessage('error', data.message || 'Purchase failed.');
                    setTimeout(() => {
                        window.location.href = '<?= $urlval?>pages/add-money/index.php';
                    }, 2000);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred.');
            }
        },
        function () {
            console.log('Purchase canceled');
        }
    ).set('labels', { ok: 'Confirm', cancel: 'Cancel' });
}


    function removeAllFromCart() {
    fetch('<?= $urlval?>ajax/removeallfromcart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ action: 'removeAll' }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartSidebar(data.cartItems, data.total);
            updateCartCount(); 
        } else {
            alert(data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}


const cartIcon = document.getElementById('cartIcon');
const cartSidebar = document.getElementById('cartSidebar');
const closeSidebar = document.getElementById('closeSidebar');


cartIcon.addEventListener('click', (event) => {
    cartSidebar.classList.add('open');
    event.stopPropagation(); 
});


closeSidebar.addEventListener('click', (event) => {
    cartSidebar.classList.remove('open');
    event.stopPropagation(); 
});


cartSidebar.addEventListener('click', (event) => {
    event.stopPropagation(); 
});


document.addEventListener('click', () => {
    if (cartSidebar.classList.contains('open')) {
        cartSidebar.classList.remove('open');
    }
});


</script>




