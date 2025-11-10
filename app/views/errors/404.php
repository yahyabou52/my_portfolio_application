<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found | Portfolio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= url('assets/css/style.css') ?>" rel="stylesheet">
    <style>
        .error-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary-color) 0%, #9D50BB 100%);
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
    </style>
</head>
<body>
    <div class="error-page">
        <div class="error-content">
            <div class="error-illustration">
                <i class="bi bi-emoji-frown" style="font-size: 4rem;"></i>
            </div>
            
            <div class="error-code">404</div>
            
            <h1 class="error-title">Page Not Found</h1>
            
            <p class="error-description">
                Oops! The page you're looking for seems to have vanished into the digital void. 
                Don't worry, even the best designs sometimes take unexpected turns.
            </p>
            
            <div class="error-actions">
                <a href="<?= url('') ?>" class="btn-home">
                    <i class="bi bi-house"></i>
                    Take Me Home
                </a>
                
                <a href="javascript:history.back()" class="btn-back">
                    <i class="bi bi-arrow-left"></i>
                    Go Back
                </a>
            </div>
            
            <div class="mt-4">
                <p class="small opacity-75">
                    If you believe this is an error, please 
                    <a href="<?= url('contact') ?>" class="text-white fw-bold">contact me</a>
                </p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Add some interactive effects
        document.addEventListener('DOMContentLoaded', function() {
            const errorCode = document.querySelector('.error-code');
            
            // Add a subtle hover effect to the error code
            errorCode.addEventListener('mouseenter', function() {
                this.style.transform = 'scale(1.05) rotate(-2deg)';
                this.style.transition = 'transform 0.3s ease';
            });
            
            errorCode.addEventListener('mouseleave', function() {
                this.style.transform = 'scale(1) rotate(0deg)';
            });
            
            // Add a floating animation to the illustration
            const illustration = document.querySelector('.error-illustration i');
            setInterval(() => {
                illustration.style.transform = 'translateY(-10px)';
                setTimeout(() => {
                    illustration.style.transform = 'translateY(0)';
                }, 1000);
            }, 2000);
            
            // Add transition effect to illustration
            illustration.style.transition = 'transform 1s ease-in-out';
        });
    </script>
</body>
</html>