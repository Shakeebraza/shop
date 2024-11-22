document.addEventListener('DOMContentLoaded', () => {
    function refreshCreditCards() {
        const filterData = new FormData(document.querySelector('#credit-card-filters'));

        fetch('fetch_cards.php', {
            method: 'POST',
            body: filterData
        })
            .then(response => response.json())
            .then(data => {
                const creditCardList = document.querySelector('#credit-card-list');
                creditCardList.innerHTML = ''; // Clear current content

                if (data.length > 0) {
                    data.forEach(card => {
                        creditCardList.innerHTML += `
                            <div class="credit-card-container">
                                <div class="credit-card-info">
                                    <div><span class="label">Type:</span>
                                        <img src="${card.image_path}" alt="${card.card_type} logo" class="card-logo">
                                    </div>
                                    <div><span class="label">BIN:</span> ${card.card_number.substr(0, 6)}</div>
                                    <div><span class="label">Exp Date:</span> ${card.mm_exp}/${card.yyyy_exp}</div>
                                    <div><span class="label">Country:</span> ${card.country}</div>
                                    <div><span class="label">State:</span> ${card.state || 'N/A'}</div>
                                    <div><span class="label">City:</span> ${card.city || 'N/A'}</div>
                                    <div><span class="label">Zip:</span> ${card.zip ? card.zip.substr(0, 3) + '***' : 'N/A'}</div>
                                    <div><span class="label">Price:</span> $${card.price}</div>
                                    <div>
                                        <a href="buy_card.php?id=${card.id}" 
                                           class="buy-button" 
                                           onclick="return confirm('Are you sure you want to buy this card?');">Buy</a>
                                    </div>
                                </div>
                            </div>`;
                    });
                } else {
                    creditCardList.innerHTML = `<p>No credit cards available.</p>`;
                }
            })
            .catch(error => console.error('Error fetching credit cards:', error));
    }

    // Start the refresh interval with a 3-second interval
    let refreshInterval = setInterval(refreshCreditCards, 3000);

    const creditCardList = document.querySelector('#credit-card-list');
    if (creditCardList) {
        // Desktop: Pause refresh on mouse hover and resume on mouse leave
        creditCardList.addEventListener('mouseover', () => clearInterval(refreshInterval));
        creditCardList.addEventListener('mouseleave', () => refreshInterval = setInterval(refreshCreditCards, 3000));

        // Mobile: Pause refresh on touch and resume on touch end
        creditCardList.addEventListener('touchstart', () => clearInterval(refreshInterval)); // Pause on touch start
        creditCardList.addEventListener('touchend', () => refreshInterval = setInterval(refreshCreditCards, 3000)); // Resume on touch end

        // Pause refresh on scroll (for both desktop and mobile)
        creditCardList.addEventListener('scroll', () => clearInterval(refreshInterval));

        // Resume refresh after user stops scrolling for 1 second
        let scrollTimeout;
        creditCardList.addEventListener('scroll', () => {
            clearInterval(refreshInterval); // Pause on scroll
            clearTimeout(scrollTimeout); // Reset scroll timeout
            scrollTimeout = setTimeout(() => refreshInterval = setInterval(refreshCreditCards, 3000), 1000); // Resume after 1s
        });
    }

    // Initial load
    refreshCreditCards();

    // Add event listener to the filter form to refresh instantly on filter change
    const filterForm = document.querySelector('#credit-card-filters');
    if (filterForm) {
        filterForm.addEventListener('input', refreshCreditCards); // Refresh immediately when any filter changes
    }
});
