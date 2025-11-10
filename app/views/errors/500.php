<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Server Error | Portfolio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= url('assets/css/style.css') ?>" rel="stylesheet">
    <style>
        .error-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
            color: white;
            text-align: center;
        }
        
        .error-content {
            max-width: 500px;
            padding: 2rem;
        }
        
        .error-code {
            font-size: 8rem;
            font-weight: 900;
            line-height: 1;
            margin-bottom: 1rem;
            opacity: 0.8;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .error-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
            font-family: 'Playfair Display', serif;
        }
        
        .error-description {
            font-size: 1.1rem;
            margin-bottom: 2rem;
            opacity: 0.9;
            line-height: 1.6;
        }
        
        .error-actions {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            align-items: center;
        }
        
        .btn-home, .btn-back {
            padding: 0.75rem 2rem;
            font-weight: 600;
            border-radius: 50px;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-home {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 2px solid rgba(255,255,255,0.3);
        }
        
        .btn-home:hover {
            background: rgba(255,255,255,0.3);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }
        
        .btn-back {
            background: transparent;
            color: rgba(255,255,255,0.8);
            border: 2px solid rgba(255,255,255,0.3);
        }
        
        .btn-back:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        
        .error-illustration {
            margin-bottom: 2rem;
            opacity: 0.6;
        }
        
        .error-details {
            margin-top: 2rem;
            padding: 1rem;
            background: rgba(0,0,0,0.2);
            border-radius: 0.5rem;
            font-size: 0.9rem;
            opacity: 0.8;
        }
        
        @media (max-width: 768px) {
            .error-code {
                font-size: 6rem;
            }
            
            .error-title {
                font-size: 1.5rem;
            }
            
            .error-description {
                font-size: 1rem;
            }
            
            .error-content {
                padding: 1rem;
            }
        }
        
        /* Animation */
        .error-content {
            animation: fadeInUp 0.8s ease-out;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Glitch effect for error code */
        .error-code {
            position: relative;
        }
        
        .error-code::before,
        .error-code::after {
            content: "500";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0.8;
        }
        
        .error-code::before {
            animation: glitch-1 2s infinite;
            color: #ff0040;
        }
        
        .error-code::after {
            animation: glitch-2 2s infinite;
            color: #00ffff;
        }
        
        @keyframes glitch-1 {
            0%, 14%, 15%, 49%, 50%, 99%, 100% {
                transform: translateX(0);
            }
            15%, 49% {
                transform: translateX(-2px);
            }
        }
        
        @keyframes glitch-2 {
            0%, 20%, 21%, 62%, 63%, 99%, 100% {
                transform: translateX(0);
            }
            21%, 62% {
                transform: translateX(2px);
            }
        }
    </style>
</head>
<body>
    <div class="error-page">
        <div class="error-content">
            <div class="error-illustration">
                <i class="bi bi-exclamation-triangle" style="font-size: 4rem;"></i>
            </div>
            
            <div class="error-code">500</div>
            
            <h1 class="error-title">Internal Server Error</h1>
            
            <p class="error-description">
                Something went wrong on our end. Our servers are experiencing some technical difficulties, 
                but don't worry - our team has been notified and is working to fix the issue.
            </p>
            
            <?php if (isset($_GET['debug']) && $_GET['debug'] === '1'): ?>
                <div class="error-details mt-3">
                    <strong>Debug Information:</strong><br>
                    <?php 
                    // Show the last few lines of error log
                    $errorLog = 'C:\\xampp\\apache\\logs\\error.log';
                    if (file_exists($errorLog)) {
                        $lines = file($errorLog);
                        $lastLines = array_slice($lines, -5);
                        foreach ($lastLines as $line) {
                            if (strpos($line, 'PHP') !== false) {
                                echo '<small>' . htmlspecialchars($line) . '</small><br>';
                            }
                        }
                    }
                    ?>
                </div>
            <?php endif; ?>
            
            <div class="error-actions">
                <a href="<?= url('') ?>" class="btn-home">
                    <i class="bi bi-house"></i>
                    Take Me Home
                </a>
                
                <a href="javascript:location.reload()" class="btn-back">
                    <i class="bi bi-arrow-clockwise"></i>
                    Try Again
                </a>
            </div>
            
            <div class="error-details">
                <p class="mb-2"><strong>What can you do?</strong></p>
                <ul class="list-unstyled mb-0 text-start">
                    <li>• Try refreshing the page</li>
                    <li>• Check back in a few minutes</li>
                    <li>• Contact me if the problem persists</li>
                </ul>
            </div>
            
            <div class="mt-4">
                <p class="small opacity-75">
                    If you continue to experience issues, please 
                    <a href="<?= url('contact') ?>" class="text-white fw-bold">contact me</a>
                </p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Add some interactive effects
        document.addEventListener('DOMContentLoaded', function() {
            const illustration = document.querySelector('.error-illustration i');
            
            // Add a pulsing animation to the warning icon
            setInterval(() => {
                illustration.style.transform = 'scale(1.1)';
                illustration.style.color = '#ffcc00';
                setTimeout(() => {
                    illustration.style.transform = 'scale(1)';
                    illustration.style.color = '';
                }, 500);
            }, 2000);
            
            // Add transition effect to illustration
            illustration.style.transition = 'all 0.5s ease-in-out';
            
            // Auto-retry functionality
            let retryCount = 0;
            const maxRetries = 3;
            
            // Add retry counter if needed
            if (window.location.search.includes('retry=')) {
                retryCount = parseInt(window.location.search.match(/retry=(\d+)/)[1]);
            }
            
            // Show retry information
            if (retryCount > 0) {
                const retryInfo = document.createElement('div');
                retryInfo.className = 'mt-3 small opacity-75';
                retryInfo.innerHTML = `<i class="bi bi-info-circle me-1"></i>Retry attempt ${retryCount} of ${maxRetries}`;
                document.querySelector('.error-details').appendChild(retryInfo);
            }
        });
        
        // Log error for debugging (if in development mode)
        console.error('500 Internal Server Error occurred');
        
        // Optional: Send error report to analytics
        if (typeof gtag !== 'undefined') {
            gtag('event', 'exception', {
                'description': '500_internal_server_error',
                'fatal': false
            });
        }
    </script>
</body>
</html>