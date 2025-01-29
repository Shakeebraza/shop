function copyCardInfo(cardId) {
    const row = document.getElementById(`card-${cardId}`);

    if (!row) {
        console.error('Row not found!');
        return;
    }

    const cells = row.querySelectorAll('td');
    let cardDetails = '';
    cells.forEach((cell, index) => {
        if (index < cells.length - 1) { 
            cardDetails += cell.textContent.trim();
            if (index < cells.length - 2) {
                cardDetails += '|'; 
            }
        }
    });

    console.log('Card Details:', cardDetails);

    if (cardDetails.trim() === '') {
        console.error('No card details to copy!');
        return;
    }

    // Check if navigator.clipboard is available
    if (navigator.clipboard) {
        navigator.clipboard.writeText(cardDetails)
            .then(() => {
                const button = row.querySelector('button');
                if (button) {
                    button.style.backgroundColor = 'green';
                    button.style.color = 'white';
                    const originalText = button.textContent;
                    button.textContent = 'Copied!';

                    setTimeout(() => {
                        button.style.backgroundColor = '';
                        button.style.color = '';
                        button.textContent = originalText;
                    }, 2000);
                }
            })
            .catch(err => {
                console.error('Failed to copy text:', err);
            });
    } else {
        console.error('Clipboard API not available. Ensure your site is served over HTTPS.');
    }
}





function copyDumpInfo(dumpId) {
    const row = document.getElementById(`dump-${dumpId}`);

    if (!row) {
        console.error('Row not found!');
        return;
    }

    // Collect all td values in the row
    const cells = row.querySelectorAll('td');
    let dumpDetails = Array.from(cells)
        .map(cell => cell.textContent.trim())
        .join('|'); // Join with a | separator

    console.log('Dump Details:', dumpDetails);

    if (dumpDetails.trim() === '') {
        console.error('No dump details to copy!');
        return;
    }

    // Copy the dump details to the clipboard
    navigator.clipboard.writeText(dumpDetails)
        .then(() => {
            const button = row.querySelector('.copy-button');
            if (button) {
                // Change button appearance
                button.style.backgroundColor = 'green';
                button.style.color = 'white';
                const originalText = button.textContent;
                button.textContent = 'Copied!';
                
                // Reset button appearance after 2 seconds
                setTimeout(() => {
                    button.style.backgroundColor = '';
                    button.style.color = '';
                    button.textContent = originalText;
                }, 2000);
            }
        })
        .catch(err => {
            console.error('Failed to copy text:', err);
        });
}
