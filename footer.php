<!-- Footer -->
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


        const cartIcon = document.getElementById('cartIcon');
        const cartSidebar = document.getElementById('cartSidebar');
        const closeSidebar = document.getElementById('closeSidebar');

       
        cartIcon.addEventListener('click', () => {
            cartSidebar.classList.add('open'); 
        });


        closeSidebar.addEventListener('click', () => {
            cartSidebar.classList.remove('open');
        });
        document.addEventListener('DOMContentLoaded', function () {
            fetch('<?= $urlval ?>getcart.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateCartSidebar(data.cartItems, data.total);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        });

        function updateCartSidebar(cartItems, total) {
            const cartItemsContainer = document.getElementById('cartItems');
            const cartTotal = document.getElementById('cartTotal');

            cartItemsContainer.innerHTML = '';

            
            cartItems.forEach(item => {
                const cartItem = document.createElement('div');
                cartItem.className = 'cart-item';
                cartItem.innerHTML = `
                    <img src="${item.image}" alt="Item Image" style="width: 50px; height: 50px; object-fit: cover;">
                    <div class="cart-item-details">
                        <h4>${item.name}</h4>
                        <p>$${item.price}</p>
                    </div>
                    <span style="cursor: pointer;" onclick="removeFromCart(${item.id})">&times;</span>
                `;
                cartItemsContainer.appendChild(cartItem);
            });

        
            cartTotal.textContent = total.toFixed(2);
        }

</script>




