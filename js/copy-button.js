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


    const tempTextArea = document.createElement('textarea');
    tempTextArea.value = cardDetails;
    document.body.appendChild(tempTextArea);


    tempTextArea.select();
    tempTextArea.setSelectionRange(0, 99999);

    // Execute the copy command
    try {
        const successful = document.execCommand('copy');
        if (successful) {
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
            alertify.success("Card details copied to clipboard!");
        } else {
            alertify.error('Failed to copy card details.');
        }
    } catch (err) {
        console.error('Failed to execute copy command', err);
    }

    // Clean up the temporary textarea
    document.body.removeChild(tempTextArea);
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

    // Create a temporary textarea element to hold the text
    const tempTextArea = document.createElement('textarea');
    tempTextArea.value = dumpDetails;
    document.body.appendChild(tempTextArea);

    // Select the text inside the textarea
    tempTextArea.select();
    tempTextArea.setSelectionRange(0, 99999); // For mobile devices

    // Execute the copy command
    try {
        const successful = document.execCommand('copy');
        if (successful) {
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
            alertify.success("Dump details copied to clipboard!");
        } else {
            alertify.error('Failed to copy dump details.');
        }
    } catch (err) {
        console.error('Failed to execute copy command', err);
    }

    // Clean up the temporary textarea
    document.body.removeChild(tempTextArea);
}

