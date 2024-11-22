    function copyCardInfo(cardId) {
        // Select the card container by ID
        const cardContainer = document.getElementById(`card-${cardId}`);
        
        if (cardContainer) {
            // Collect text from elements with the "info-field" class only
            let cardInfo = '';
            cardContainer.querySelectorAll('.info-field').forEach((element) => {
                cardInfo += element.innerText + '\n'; // Add each field's text to cardInfo
            });

            // Copy the formatted information to clipboard
            navigator.clipboard.writeText(cardInfo.trim()).then(() => {
                alert("Card information copied to clipboard!");
            }).catch(err => {
                alert("Failed to copy: " + err);
            });
        }
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
