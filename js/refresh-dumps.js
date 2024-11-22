document.addEventListener('DOMContentLoaded', () => {
    function refreshDumps() {
        const filterData = new FormData(document.querySelector('#dump-filters'));

        fetch('fetch_dump.php', {
            method: 'POST',
            body: filterData
        })
            .then(response => response.json())
            .then(data => {
                const dumpsList = document.querySelector('#dumps-list');
                dumpsList.innerHTML = ''; // Clear current content

                if (data.length > 0) {
                    data.forEach(dump => {
                        dumpsList.innerHTML += `
                            <div class="dump-container">
                                <div class="dump-info">
                                    <div><span class="label">Type:</span>
                                        <img src="${dump.image_path}" alt="${dump.card_type} logo" class="card-logo">
                                    </div>
                                    <div><span class="label">BIN:</span> ${dump.track2.substr(0, 6)}</div>
                                    <div><span class="label">Exp Date:</span> ${dump.monthexp}/${dump.yearexp}</div>
                                    <div><span class="label">PIN:</span> ${dump.pin ? 'Yes' : 'No'}</div>
                                    <div><span class="label">Country:</span> ${dump.country}</div>
                                    <div><span class="label">Price:</span> $${dump.price}</div>
                                    <div>
                                        <a href="buy_dump.php?dump_id=${dump.id}" 
                                           class="buy-button-dump" 
                                           onclick="return confirm('Are you sure you want to buy this dump?');">Buy</a>
                                    </div>
                                </div>
                            </div>`;
                    });
                } else {
                    dumpsList.innerHTML = `<p>No dumps available.</p>`;
                }
            })
            .catch(error => console.error('Error fetching dumps:', error));
    }

    // Set the refresh interval to 5 seconds
    let refreshInterval = setInterval(refreshDumps, 5000);

    const dumpsList = document.querySelector('#dumps-list');
    if (dumpsList) {
        // Desktop: Pause refresh on mouse hover and resume on mouse leave
        dumpsList.addEventListener('mouseover', () => clearInterval(refreshInterval));
        dumpsList.addEventListener('mouseleave', () => refreshInterval = setInterval(refreshDumps, 5000));

        // Mobile: Pause refresh on touch and resume on touch end
        dumpsList.addEventListener('touchstart', () => clearInterval(refreshInterval)); // Pause on touch start
        dumpsList.addEventListener('touchend', () => refreshInterval = setInterval(refreshDumps, 5000)); // Resume on touch end

        // Pause refresh on scroll (for both desktop and mobile)
        dumpsList.addEventListener('scroll', () => clearInterval(refreshInterval));

        // Resume refresh after scrolling stops for 1 second
        let scrollTimeout;
        dumpsList.addEventListener('scroll', () => {
            clearInterval(refreshInterval); // Pause on scroll
            clearTimeout(scrollTimeout); // Reset scroll timeout
            scrollTimeout = setTimeout(() => refreshInterval = setInterval(refreshDumps, 5000), 1000); // Resume after 1s
        });
    }

    // Initial load
    refreshDumps();

    // Add event listener to the filter form to refresh instantly on filter change
    const filterForm = document.querySelector('#dump-filters');
    if (filterForm) {
        filterForm.addEventListener('input', refreshDumps); // Refresh immediately when any filter changes
    }
});
