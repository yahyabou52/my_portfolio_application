<!-- Contact Header -->
<section class="contact-header py-5 mt-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <h1 class="contact-title mb-4">Let's Work Together</h1>
                <p class="contact-subtitle text-muted mb-5">
                    I'm always excited to take on new challenges and help bring ideas to life. 
                    Whether you have a project in mind or just want to chat about design, 
                    I'd love to hear from you.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Contact Content -->
<section class="contact-content py-5">
    <div class="container">
        <div class="row">
            <!-- Contact Form -->
            <div class="col-lg-8 mb-5" data-aos="fade-right">
                <div class="contact-form-wrapper">
                    <h2 class="form-title mb-4">Send Me a Message</h2>
                    
                    <!-- Form -->
                    <form method="POST" action="<?= url('contact') ?>" class="contact-form" id="contactForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Your Name *</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?= htmlspecialchars($this->getFlash('form_data')['name'] ?? '') ?>" 
                                       required>
                                <?php if ($this->getFlash('form_errors')['name'] ?? false): ?>
                                    <div class="form-error text-danger mt-1">
                                        <?= htmlspecialchars($this->getFlash('form_errors')['name']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= htmlspecialchars($this->getFlash('form_data')['email'] ?? '') ?>" 
                                       required>
                                <?php if ($this->getFlash('form_errors')['email'] ?? false): ?>
                                    <div class="form-error text-danger mt-1">
                                        <?= htmlspecialchars($this->getFlash('form_errors')['email']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject *</label>
                            <input type="text" class="form-control" id="subject" name="subject" 
                                   value="<?= htmlspecialchars($this->getFlash('form_data')['subject'] ?? '') ?>" 
                                   required>
                            <?php if ($this->getFlash('form_errors')['subject'] ?? false): ?>
                                <div class="form-error text-danger mt-1">
                                    <?= htmlspecialchars($this->getFlash('form_errors')['subject']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-4">
                            <label for="message" class="form-label">Message *</label>
                            <textarea class="form-control" id="message" name="message" rows="6" 
                                      required placeholder="Tell me about your project, goals, timeline, and any specific requirements..."><?= htmlspecialchars($this->getFlash('form_data')['message'] ?? '') ?></textarea>
                            <?php if ($this->getFlash('form_errors')['message'] ?? false): ?>
                                <div class="form-error text-danger mt-1">
                                    <?= htmlspecialchars($this->getFlash('form_errors')['message']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-send me-2"></i>
                            Send Message
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Contact Info -->
            <div class="col-lg-4" data-aos="fade-left" data-aos-delay="200">
                <div class="contact-info-wrapper">
                    <h3 class="info-title mb-4">Get In Touch</h3>
                    
                    <!-- Contact Methods -->
                    <div class="contact-methods mb-5">
                        <div class="contact-method mb-4">
                            <div class="method-icon">
                                <i class="bi bi-envelope"></i>
                            </div>
                            <div class="method-content">
                                <h5 class="method-title">Email</h5>
                                <p class="method-text">
                                    <a href="mailto:hello@alexandra-design.com">hello@alexandra-design.com</a>
                                </p>
                                <small class="text-muted">I typically respond within 24 hours</small>
                            </div>
                        </div>
                        
                        <div class="contact-method mb-4">
                            <div class="method-icon">
                                <i class="bi bi-telephone"></i>
                            </div>
                            <div class="method-content">
                                <h5 class="method-title">Phone</h5>
                                <p class="method-text">
                                    <a href="tel:+1234567890">+1 (234) 567-890</a>
                                </p>
                                <small class="text-muted">Available Mon-Fri, 9AM-6PM PST</small>
                            </div>
                        </div>
                        
                        <div class="contact-method mb-4">
                            <div class="method-icon">
                                <i class="bi bi-geo-alt"></i>
                            </div>
                            <div class="method-content">
                                <h5 class="method-title">Location</h5>
                                <p class="method-text">San Francisco, CA</p>
                                <small class="text-muted">Open to remote work worldwide</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Social Links -->
                    <div class="social-section mb-5">
                        <h5 class="social-title mb-3">Follow Me</h5>
                        <div class="social-links">
                            <a href="#" class="social-link" aria-label="LinkedIn">
                                <i class="bi bi-linkedin"></i>
                            </a>
                            <a href="#" class="social-link" aria-label="Dribbble">
                                <i class="bi bi-dribbble"></i>
                            </a>
                            <a href="#" class="social-link" aria-label="Behance">
                                <i class="bi bi-behance"></i>
                            </a>
                            <a href="#" class="social-link" aria-label="Instagram">
                                <i class="bi bi-instagram"></i>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Response Time -->
                    <div class="response-info">
                        <div class="info-card">
                            <div class="card-icon">
                                <i class="bi bi-clock"></i>
                            </div>
                            <div class="card-content">
                                <h6 class="card-title">Average Response Time</h6>
                                <p class="card-text">Within 24 hours</p>
                            </div>
                        </div>
                        
                        <div class="info-card">
                            <div class="card-icon">
                                <i class="bi bi-calendar-check"></i>
                            </div>
                            <div class="card-content">
                                <h6 class="card-title">Availability</h6>
                                <p class="card-text">Currently accepting new projects</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="contact-faq py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <h2 class="section-title mb-3">Frequently Asked Questions</h2>
                <p class="section-subtitle text-muted mb-5">
                    Here are answers to some common questions about working together.
                </p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="accordion" id="contactFaqAccordion">
                    <div class="accordion-item" data-aos="fade-up" data-aos-delay="100">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" 
                                    data-bs-target="#faq1" aria-expanded="true">
                                What information should I include in my project inquiry?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#contactFaqAccordion">
                            <div class="accordion-body">
                                Please include details about your project goals, target audience, timeline, 
                                budget range, and any specific requirements or preferences. The more information 
                                you provide, the better I can understand your needs and provide an accurate proposal.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item" data-aos="fade-up" data-aos-delay="200">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                    data-bs-target="#faq2" aria-expanded="false">
                                What's your typical project timeline?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#contactFaqAccordion">
                            <div class="accordion-body">
                                Project timelines vary based on scope and complexity. Simple projects may take 
                                1-2 weeks, while comprehensive design systems or complex applications can take 
                                4-8 weeks. I'll provide a detailed timeline during our initial consultation.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item" data-aos="fade-up" data-aos-delay="300">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                    data-bs-target="#faq3" aria-expanded="false">
                                Do you work with clients remotely?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#contactFaqAccordion">
                            <div class="accordion-body">
                                Yes, I work with clients worldwide. I use various collaboration tools like 
                                Figma, Slack, Zoom, and project management platforms to ensure smooth 
                                communication and project delivery regardless of location.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item" data-aos="fade-up" data-aos-delay="400">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                    data-bs-target="#faq4" aria-expanded="false">
                                What's your payment structure?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#contactFaqAccordion">
                            <div class="accordion-body">
                                I typically work with a 50% upfront payment and 50% upon completion for smaller 
                                projects. For larger projects, we can arrange milestone-based payments. 
                                All payment terms will be clearly outlined in the project proposal.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <h2 class="cta-title mb-3">Ready to Start Your Project?</h2>
                <p class="cta-subtitle text-muted mb-4">
                    Don't hesitate to reach out. I'm here to help bring your ideas to life 
                    and create exceptional user experiences.
                </p>
                <div class="cta-actions">
                    <a href="#contactForm" class="btn btn-primary btn-lg me-3">
                        <i class="bi bi-envelope me-2"></i>
                        Send Message
                    </a>
                    <a href="<?= url('portfolio') ?>" class="btn btn-outline-primary btn-lg">
                        <i class="bi bi-collection me-2"></i>
                        View My Work
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>