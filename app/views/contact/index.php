<!-- Contact Header -->
<?php if (WebsiteContent::visible('contact.section.contact-header')): ?><section data-section-key="contact.section.contact-header" class="contact-header py-5 mt-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <h1 class="contact-title mb-4"><?= site_text('contact.text.28e7ac9a04dd', 'Let\'s Work Together') ?></h1>
                <p class="contact-subtitle text-muted mb-5"><?= site_text('contact.text.ec3ea0a029e2', 'I\'m always excited to take on new challenges and help bring ideas to life.
                    Whether you have a project in mind or just want to chat about design,
                    I\'d love to hear from you.') ?></p>
            </div>
        </div>
    </div>
</section><?php endif; ?>

<!-- Contact Content -->
<?php if (WebsiteContent::visible('contact.section.contact-content')): ?><section data-section-key="contact.section.contact-content" class="contact-content py-5">
    <div class="container">
        <div class="row">
            <!-- Contact Form -->
            <div class="col-lg-8 mb-5" data-aos="fade-right">
                <div class="contact-form-wrapper">
                    <h2 class="form-title mb-4"><?= site_text('contact.text.9841c4c43c70', 'Send Me a Message') ?></h2>

                    <!-- Form -->
                    <form method="POST" action="<?= url('contact') ?>" class="contact-form" id="contactForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label"><?= site_text('contact.text.60e93b1b3bfd', 'Your Name *') ?></label>
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
                                <label for="email" class="form-label"><?= site_text('contact.text.ff92ba7e8f35', 'Email Address *') ?></label>
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
                            <label for="subject" class="form-label"><?= site_text('contact.text.e7c6a032ad15', 'Subject *') ?></label>
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
                            <label for="message" class="form-label"><?= site_text('contact.text.8addb891254a', 'Message *') ?></label>
                            <textarea class="form-control" id="message" name="message" rows="6"
                                      required placeholder="Tell me about your project, goals, timeline, and any specific requirements..."><?= htmlspecialchars($this->getFlash('form_data')['message'] ?? '') ?></textarea>
                            <?php if ($this->getFlash('form_errors')['message'] ?? false): ?>
                                <div class="form-error text-danger mt-1">
                                    <?= htmlspecialchars($this->getFlash('form_errors')['message']) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-send me-2"></i><?= site_text('contact.text.6dcd151222c0', 'Send Message') ?></button>
                    </form>
                </div>
            </div>

            <!-- Contact Info -->
            <div class="col-lg-4" data-aos="fade-left" data-aos-delay="200">
                <div class="contact-info-wrapper">
                    <h3 class="info-title mb-4"><?= site_text('contact.text.26e00b08d068', 'Get In Touch') ?></h3>

                    <!-- Contact Methods -->
                    <div class="contact-methods mb-5">
                        <div class="contact-method mb-4">
                            <div class="method-icon">
                                <i class="bi bi-envelope"></i>
                            </div>
                            <div class="method-content">
                                <h5 class="method-title"><?= site_text('contact.text.84add5b29527', 'Email') ?></h5>
                                <p class="method-text">
                                    <a href="mailto:<?= htmlspecialchars($contactSettings['contact_email'] ?? '') ?>"><?= htmlspecialchars($contactSettings['contact_email'] ?? '') ?></a>
                                </p>
                                <small class="text-muted"><?= site_text('contact.text.f0633a0c503f', 'I typically respond within 24 hours') ?></small>
                            </div>
                        </div>

                        <div class="contact-method mb-4">
                            <div class="method-icon">
                                <i class="bi bi-telephone"></i>
                            </div>
                            <div class="method-content">
                                <h5 class="method-title"><?= site_text('contact.text.77064d526523', 'Phone') ?></h5>
                                <p class="method-text">
                                    <a href="tel:<?= htmlspecialchars($contactSettings['contact_phone'] ?? '') ?>"><?= htmlspecialchars($contactSettings['contact_phone'] ?? '') ?></a>
                                </p>
                                <small class="text-muted"><?= site_text('contact.text.6b618217ab15', 'Available Mon-Fri, 9AM-6PM PST') ?></small>
                            </div>
                        </div>

                        <div class="contact-method mb-4">
                            <div class="method-icon">
                                <i class="bi bi-geo-alt"></i>
                            </div>
                            <div class="method-content">
                                <h5 class="method-title"><?= site_text('contact.text.d219c68101f5', 'Location') ?></h5>
                                <p class="method-text"><?= htmlspecialchars($contactSettings['contact_address'] ?? '') ?></p>
                                <small class="text-muted"><?= site_text('contact.text.3b501e11dd10', 'Open to remote work worldwide') ?></small>
                            </div>
                        </div>
                    </div>

                    <!-- Social Links -->
                    <div class="social-section mb-5">
                        <h5 class="social-title mb-3"><?= site_text('contact.text.468d566ab6bc', 'Follow Me') ?></h5>
                        <div class="social-links"><?php foreach ($socialLinks ?? [] as $link): ?><a class="social-link" href="<?= htmlspecialchars($link['url']) ?>" aria-label="<?= htmlspecialchars($link['platform']) ?>"><i class="bi bi-<?= htmlspecialchars($link['platform']) ?>"></i></a><?php endforeach; ?></div>
                    </div>

                    <!-- Response Time -->
                    <div class="response-info">
                        <div class="info-card">
                            <div class="card-icon">
                                <i class="bi bi-clock"></i>
                            </div>
                            <div class="card-content">
                                <h6 class="card-title"><?= site_text('contact.text.01aec4c6c96b', 'Average Response Time') ?></h6>
                                <p class="card-text"><?= site_text('contact.text.5fdc37d3b84c', 'Within 24 hours') ?></p>
                            </div>
                        </div>

                        <div class="info-card">
                            <div class="card-icon">
                                <i class="bi bi-calendar-check"></i>
                            </div>
                            <div class="card-content">
                                <h6 class="card-title"><?= site_text('contact.text.681b5b5ae10b', 'Availability') ?></h6>
                                <p class="card-text"><?= site_text('contact.text.0ca993eab5e7', 'Currently accepting new projects') ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section><?php endif; ?>

<!-- FAQ Section -->
<?php if (WebsiteContent::visible('contact.section.contact-faq')): ?><section data-section-key="contact.section.contact-faq" class="contact-faq py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <h2 class="section-title mb-3"><?= site_text('contact.text.d790b402d79a', 'Frequently Asked Questions') ?></h2>
                <p class="section-subtitle text-muted mb-5"><?= site_text('contact.text.ef4885ab9743', 'Here are answers to some common questions about working together.') ?></p>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="accordion" id="contactFaqAccordion">
                    <div class="accordion-item" data-aos="fade-up" data-aos-delay="100">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#faq1" aria-expanded="true"><?= site_text('contact.text.03e3412958e6', 'What information should I include in my project inquiry?') ?></button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#contactFaqAccordion">
                            <div class="accordion-body"><?= site_text('contact.text.162e4b49dedd', 'Please include details about your project goals, target audience, timeline,
                                budget range, and any specific requirements or preferences. The more information
                                you provide, the better I can understand your needs and provide an accurate proposal.') ?></div>
                        </div>
                    </div>

                    <div class="accordion-item" data-aos="fade-up" data-aos-delay="200">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#faq2" aria-expanded="false"><?= site_text('contact.text.c9b0053a27c9', 'What\'s your typical project timeline?') ?></button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#contactFaqAccordion">
                            <div class="accordion-body"><?= site_text('contact.text.38a6a973246e', 'Project timelines vary based on scope and complexity. Simple projects may take
                                1-2 weeks, while comprehensive design systems or complex applications can take
                                4-8 weeks. I\'ll provide a detailed timeline during our initial consultation.') ?></div>
                        </div>
                    </div>

                    <div class="accordion-item" data-aos="fade-up" data-aos-delay="300">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#faq3" aria-expanded="false"><?= site_text('contact.text.56e3110de4af', 'Do you work with clients remotely?') ?></button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#contactFaqAccordion">
                            <div class="accordion-body"><?= site_text('contact.text.c8a2b7b4813c', 'Yes, I work with clients worldwide. I use various collaboration tools like
                                Figma, Slack, Zoom, and project management platforms to ensure smooth
                                communication and project delivery regardless of location.') ?></div>
                        </div>
                    </div>

                    <div class="accordion-item" data-aos="fade-up" data-aos-delay="400">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#faq4" aria-expanded="false"><?= site_text('contact.text.9aeae533bb68', 'What\'s your payment structure?') ?></button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#contactFaqAccordion">
                            <div class="accordion-body"><?= site_text('contact.text.31b120bb82df', 'I typically work with a 50% upfront payment and 50% upon completion for smaller
                                projects. For larger projects, we can arrange milestone-based payments.
                                All payment terms will be clearly outlined in the project proposal.') ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section><?php endif; ?>

<!-- CTA Section -->
<?php if (WebsiteContent::visible('contact.section.cta-section')): ?><section data-section-key="contact.section.cta-section" class="cta-section py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <h2 class="cta-title mb-3"><?= site_text('contact.text.7aaff76b03b8', 'Ready to Start Your Project?') ?></h2>
                <p class="cta-subtitle text-muted mb-4"><?= site_text('contact.text.1ffe8ae1e28d', 'Don\'t hesitate to reach out. I\'m here to help bring your ideas to life
                    and create exceptional user experiences.') ?></p>
                <div class="cta-actions">
                    <a href="#contactForm" class="btn btn-primary btn-lg me-3">
                        <i class="bi bi-envelope me-2"></i><?= site_text('contact.text.6dcd151222c0', 'Send Message') ?></a>
                    <a href="<?= url('portfolio') ?>" class="btn btn-outline-primary btn-lg">
                        <i class="bi bi-collection me-2"></i><?= site_text('contact.text.eeab5301df0a', 'View My Work') ?></a>
                </div>
            </div>
        </div>
    </div>
</section><?php endif; ?>