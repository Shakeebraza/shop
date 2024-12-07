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

    let refreshInterval = setInterval(refreshDumps, 5000);

    const dumpsList = document.querySelector('#dumps-list');
    if (dumpsList) {

        dumpsList.addEventListener('mouseover', () => clearInterval(refreshInterval));
        dumpsList.addEventListener('mouseleave', () => refreshInterval = setInterval(refreshDumps, 5000));

      
        dumpsList.addEventListener('touchstart', () => clearInterval(refreshInterval)); 
        dumpsList.addEventListener('touchend', () => refreshInterval = setInterval(refreshDumps, 5000)); 

 
        dumpsList.addEventListener('scroll', () => clearInterval(refreshInterval));


        let scrollTimeout;
        dumpsList.addEventListener('scroll', () => {
            clearInterval(refreshInterval); 
            clearTimeout(scrollTimeout); 
            scrollTimeout = setTimeout(() => refreshInterval = setInterval(refreshDumps, 5000), 1000); 
        });
    }

    refreshDumps();

    
    const filterForm = document.querySelector('#dump-filters');
    if (filterForm) {
        filterForm.addEventListener('input', refreshDumps); 
    }
});
