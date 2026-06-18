/**
 * LocalMart Frontend Interactions Handler
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Home Page Live Search
    const searchInput = document.getElementById('store-search-input');
    const storeCards = document.querySelectorAll('.store-card');
    const storeCountEl = document.getElementById('store-count');

    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase().trim();
            let visibleCount = 0;

            storeCards.forEach(card => {
                const name = card.getAttribute('data-name') || '';
                const desc = card.getAttribute('data-desc') || '';

                if (name.includes(query) || desc.includes(query)) {
                    card.style.display = 'flex';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            if (storeCountEl) {
                storeCountEl.textContent = visibleCount;
            }
        });
    }

    // 2. QR Scanner Handling
    const btnStartScan = document.getElementById('btn-start-scan');
    const btnStopScan = document.getElementById('btn-stop-scan');
    const scannerPlaceholder = document.getElementById('scanner-placeholder');
    const scannerLaser = document.getElementById('scanner-laser');
    const scannerTarget = document.getElementById('scanner-target');
    
    let html5QrcodeScanner = null;

    if (btnStartScan && btnStopScan) {
        btnStartScan.addEventListener('click', () => {
            // Instantiate QR scanner on the 'reader' element
            html5QrcodeScanner = new Html5Qrcode("reader");

            // Hide placeholder & show overlay visual guides
            scannerPlaceholder.style.display = 'none';
            scannerLaser.style.display = 'block';
            scannerTarget.style.display = 'flex';
            btnStartScan.style.display = 'none';
            btnStopScan.style.display = 'inline-flex';

            // Success scanner callback
            const onScanSuccess = (decodedText, decodedResult) => {
                console.log(`Scan successful: ${decodedText}`);
                
                // Stop scanner
                stopScanning();

                // Check if scanned QR content is a URL or a token code
                let token = decodedText;
                try {
                    // Try parsing as URL, e.g. http://localhost/localmart/store.php?code=token_greens
                    const url = new URL(decodedText);
                    const codeParam = url.searchParams.get('code');
                    if (codeParam) {
                        token = codeParam;
                    }
                } catch (e) {
                    // Not a URL, use direct decoded text token
                }

                // Redirect to store page
                window.location.href = `store.php?code=${encodeURIComponent(token)}`;
            };

            // Error scanner callback (can fire frequently, keep quiet)
            const onScanError = (errorMessage) => {
                // Console.log(errorMessage);
            };

            // Start using rear camera if available, fallback to user camera
            html5QrcodeScanner.start(
                { facingMode: "environment" }, 
                {
                    fps: 10,
                    qrbox: { width: 220, height: 220 }
                },
                onScanSuccess,
                onScanError
            ).catch(err => {
                console.error("Camera startup failed:", err);
                alert("Could not access your camera. Make sure permissions are granted and you are running on localhost or HTTPS.");
                stopScanning();
            });
        });

        btnStopScan.addEventListener('click', () => {
            stopScanning();
        });
    }

    function stopScanning() {
        if (html5QrcodeScanner) {
            html5QrcodeScanner.stop().then(() => {
                console.log("Scanner stopped.");
            }).catch(err => {
                console.error("Failed to stop scanner cleanly:", err);
            });
        }
        
        // Restore layout
        if (scannerPlaceholder) scannerPlaceholder.style.display = 'flex';
        if (scannerLaser) scannerLaser.style.display = 'none';
        if (scannerTarget) scannerTarget.style.display = 'none';
        if (btnStartScan) btnStartScan.style.display = 'inline-flex';
        if (btnStopScan) btnStopScan.style.display = 'none';
    }

    // 3. Demo Manual Token redirection
    const btnManualGo = document.getElementById('btn-manual-go');
    const manualTokenInput = document.getElementById('manual-token');

    if (btnManualGo && manualTokenInput) {
        btnManualGo.addEventListener('click', () => {
            const token = manualTokenInput.value.trim();
            if (token) {
                window.location.href = `store.php?code=${encodeURIComponent(token)}`;
            } else {
                alert("Please enter a valid store token.");
            }
        });

        manualTokenInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                btnManualGo.click();
            }
        });
    }
});
