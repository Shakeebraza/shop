function copyCardInfo(cardId) {
    const row = document.getElementById(`card-${cardId}`);

    if (!row) {
        alert('Row not found!');
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
        alert('No card details to copy!');
        return;
    }


    navigator.clipboard.writeText(cardDetails)
        .then(() => {
            alert('Card details copied to clipboard!');
        })
        .catch(err => {
            console.error('Failed to copy text:', err);
            alert('Failed to copy card details!');
        });
}


    function copyDumpInfo(dumpId) {
        // Select the dump container by ID
        const dumpContainer = document.getElementById(`dump-${dumpId}`);
        
        if (dumpContainer) {
            // Collect text from elements with the "info-field" class only
            let dumpInfo = '';
            dumpContainer.querySelectorAll('.info-field').forEach((element) => {
                dumpInfo += element.innerText + '\n'; // Add each field's text to dumpInfo
            });

            // Copy the formatted information to clipboard
            navigator.clipboard.writeText(dumpInfo.trim()).then(() => {
                alert("Dump information copied to clipboard!");
            }).catch(err => {
                alert("Failed to copy: " + err);
            });
        }
    }
