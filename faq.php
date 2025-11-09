<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ - M25 Travel & Tour Global Services</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #13357B;
            --secondary-color: #1e4a8c;
            --accent-color: #f8f9fa;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 80px 0;
            text-align: center;
        }
        
        .faq-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 60px 20px;
        }
        
        .faq-section {
            margin-bottom: 50px;
        }
        
        .faq-section h3 {
            color: var(--primary-color);
            border-bottom: 3px solid var(--primary-color);
            padding-bottom: 15px;
            margin-bottom: 30px;
            font-size: 1.8rem;
            font-weight: 600;
        }
        
        .faq-item {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-left: 4px solid var(--primary-color);
        }
        
        .faq-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }
        
        .faq-question {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 12px;
            font-size: 1.1rem;
        }
        
        .faq-answer {
            color: #555;
            line-height: 1.7;
            font-size: 1rem;
        }
        
        .cta-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border-radius: 20px;
            padding: 50px;
            text-align: center;
            margin: 60px 0;
            box-shadow: 0 10px 30px rgba(19, 53, 123, 0.3);
        }
        
        .cta-section h3 {
            font-size: 2.2rem;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .cta-section p {
            font-size: 1.2rem;
            margin-bottom: 30px;
            opacity: 0.9;
        }
        
        .btn-cta {
            background: white;
            color: var(--primary-color);
            padding: 15px 40px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 50px;
            border: none;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-cta:hover {
            background: var(--accent-color);
            color: var(--primary-color);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }
        
        .search-box {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 40px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .search-input {
            border: 2px solid #e9ecef;
            border-radius: 50px;
            padding: 15px 25px;
            font-size: 1.1rem;
            transition: border-color 0.3s ease;
        }
        
        .search-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(19, 53, 123, 0.25);
        }
        
        .navbar-custom {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 15px 0;
        }
        
        .navbar-brand {
            color: var(--primary-color) !important;
            font-weight: 700;
            font-size: 1.5rem;
        }
        
        .nav-link {
            color: #666 !important;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        
        .nav-link:hover {
            color: var(--primary-color) !important;
        }
        
        .highlight {
            background-color: #fff3cd;
            padding: 2px 4px;
            border-radius: 3px;
        }
        
        @media (max-width: 768px) {
            .hero-section {
                padding: 50px 0;
            }
            
            .faq-container {
                padding: 40px 15px;
            }
            
            .cta-section {
                padding: 30px 20px;
                margin: 40px 0;
            }
            
            .cta-section h3 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-custom fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-plane me-2"></i>M25 Travel & Tour
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="services.php">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="faq.php">FAQ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="client-login.php">Client Portal</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero-section" style="margin-top: 76px;">
        <div class="container">
            <h1 class="display-4 mb-4">Frequently Asked Questions</h1>
            <h2 class="h4 mb-4">M25 Travel & Tour Global Services</h2>
            <p class="lead">Below are the most common international travel, visa processing and client support questions asked by travelers worldwide. M25 Travel & Tour now operates globally assisting clients from any country with visa advisory, document preparation, travel support and consultation services.</p>
        </div>
    </div>

    <!-- FAQ Content -->
    <div class="faq-container">
        <!-- Search Box -->
        <div class="search-box">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <input type="text" class="form-control search-input" id="faqSearch" placeholder="Search FAQ questions...">
                </div>
                <div class="col-md-4 mt-3 mt-md-0">
                    <button class="btn btn-primary w-100" onclick="searchFAQ()">
                        <i class="fas fa-search me-2"></i>Search
                    </button>
                </div>
            </div>
        </div>

        <!-- General Company Questions -->
        <div class="faq-section">
            <h3><i class="fas fa-building me-2"></i>General Company Questions</h3>
            
            <div class="faq-item">
                <div class="faq-question">1. What does M25 Travel & Tour do?</div>
                <div class="faq-answer">We assist travelers worldwide with visa documentation guidance, consultation, travel planning, itinerary, accommodation support and application coaching for tourism, work, study, business and family visas.</div>
            </div>

            <div class="faq-item">
                <div class="faq-question">2. Do you work globally or only in Ghana?</div>
                <div class="faq-answer">We are now global. Any client from any country can use our services.</div>
            </div>

            <div class="faq-item">
                <div class="faq-question">3. Can you guarantee visa approval?</div>
                <div class="faq-answer">No. No agency can guarantee visa approval. Final decision is made only by the Embassy.</div>
            </div>

            <div class="faq-item">
                <div class="faq-question">4. Which countries do you support?</div>
                <div class="faq-answer">We support visas for Canada, USA, UK, Europe Schengen, Australia, Dubai UAE, Turkey, Asia and more.</div>
            </div>

            <div class="faq-item">
                <div class="faq-question">5. Do you work with all visa types?</div>
                <div class="faq-answer">Yes — Tourist, Business, Study, Family Visit, Medical, Work Permit, Religious and long stay categories.</div>
            </div>
        </div>

        <!-- Visa Processing / Documents -->
        <div class="faq-section">
            <h3><i class="fas fa-passport me-2"></i>Visa Processing / Documents</h3>
            
            <div class="faq-item">
                <div class="faq-question">6. Do you submit the visa for me?</div>
                <div class="faq-answer">Some embassies allow representative submissions and some require biometric attendance. We guide correctly case by case.</div>
            </div>

            <div class="faq-item">
                <div class="faq-question">7. Can you help prepare my supporting documents?</div>
                <div class="faq-answer">Yes. We format, review and advise embassy compliant document standards.</div>
            </div>

            <div class="faq-item">
                <div class="faq-question">8. How long does visa processing take?</div>
                <div class="faq-answer">Every country is different — ranges from 7 days to several months depending on visa type.</div>
            </div>

            <div class="faq-item">
                <div class="faq-question">9. What type of bank statement is acceptable?</div>
                <div class="faq-answer">Official bank-issued printed statement 3–6 months with verification.</div>
            </div>

            <div class="faq-item">
                <div class="faq-question">10. Do you assist business owners applying for visas?</div>
                <div class="faq-answer">Yes. We guide business registration documents, tax, and financial evidence.</div>
            </div>

            <div class="faq-item">
                <div class="faq-question">11. Do you help corporate group travel delegations?</div>
                <div class="faq-answer">Yes. We provide corporate and business travel visa advisory globally.</div>
            </div>

            <div class="faq-item">
                <div class="faq-question">12. Can I apply if I have previous visa refusal?</div>
                <div class="faq-answer">Yes. We evaluate refusal reason, correct documentation and reapply.</div>
            </div>

            <div class="faq-item">
                <div class="faq-question">13. Is travel insurance required?</div>
                <div class="faq-answer">Most countries require travel medical insurance. We assist arrangement.</div>
            </div>

            <div class="faq-item">
                <div class="faq-question">14. Can I apply without bank statement?</div>
                <div class="faq-answer">Financial proof is required. We advise what alternative evidence is acceptable depending on category.</div>
            </div>

            <div class="faq-item">
                <div class="faq-question">15. Do you assist with police clearance certificate?</div>
                <div class="faq-answer">Yes — for countries or categories that require it.</div>
            </div>
        </div>

        <!-- Study Visas / Work Visas -->
        <div class="faq-section">
            <h3><i class="fas fa-graduation-cap me-2"></i>Study Visas / Work Visas</h3>
            
            <div class="faq-item">
                <div class="faq-question">16. Do you help students with study permit process?</div>
                <div class="faq-answer">Yes. We assist with SOP, CV, Proof of Funds structure and admissions direction.</div>
            </div>

            <div class="faq-item">
                <div class="faq-question">17. Do you assist Work Visa / Work Permits?</div>
                <div class="faq-answer">Yes — if client has genuine employer sponsor / contract.</div>
            </div>

            <div class="faq-item">
                <div class="faq-question">18. Do you provide jobs abroad?</div>
                <div class="faq-answer">No. We do not sell jobs. We assist legal visa processing only.</div>
            </div>

            <div class="faq-item">
                <div class="faq-question">19. Can you guarantee a job abroad after visa approval?</div>
                <div class="faq-answer">No. Employment outcome depends on employer — not agency.</div>
            </div>
        </div>

        <!-- Travel Support Services -->
        <div class="faq-section">
            <h3><i class="fas fa-suitcase-rolling me-2"></i>Travel Support Services</h3>
            
            <div class="faq-item">
                <div class="faq-question">20. Do you assist flight booking?</div>
                <div class="faq-answer">Yes — flight reservations + confirmed tickets purchase.</div>
            </div>

            <div class="faq-item">
                <div class="faq-question">21. Do you assist hotel booking and accommodation?</div>
                <div class="faq-answer">Yes.</div>
            </div>

            <div class="faq-item">
                <div class="faq-question">22. Can you assist itinerary planning?</div>
                <div class="faq-answer">Yes — tourism, events, food, holiday, business travel, religious tours etc.</div>
            </div>

            <div class="faq-item">
                <div class="faq-question">23. Do you assist group tours?</div>
                <div class="faq-answer">Yes — travel packages, pilgrimages, family group holidays etc.</div>
            </div>

            <div class="faq-item">
                <div class="faq-question">24. Do you assist airport arrival support?</div>
                <div class="faq-answer">Yes — meet & greet available for select destinations.</div>
            </div>
        </div>

        <!-- Payment / Service Policies -->
        <div class="faq-section">
            <h3><i class="fas fa-credit-card me-2"></i>Payment / Service Policies</h3>
            
            <div class="faq-item">
                <div class="faq-question">25. Is consultation required before beginning processing?</div>
                <div class="faq-answer">Yes. Initial assessment ensures proper profile matching and eligibility.</div>
            </div>

            <div class="faq-item">
                <div class="faq-question">26. What payment methods do you accept?</div>
                <div class="faq-answer">We accept global payment options including card, bank transfer and mobile money depending on country.</div>
            </div>

            <div class="faq-item">
                <div class="faq-question">27. Is service fee refundable if visa is denied?</div>
                <div class="faq-answer">No — service fee covers consultation and preparation work already completed.</div>
            </div>
        </div>

        <!-- Special Cases & Conditions -->
        <div class="faq-section">
            <h3><i class="fas fa-exclamation-triangle me-2"></i>Special Cases & Conditions</h3>
            
            <div class="faq-item">
                <div class="faq-question">28. Can I apply visa while outside my home country?</div>
                <div class="faq-answer">Some embassies allow this. We advise case by case.</div>
            </div>

            <div class="faq-item">
                <div class="faq-question">29. Do you assist minors' visa applications?</div>
                <div class="faq-answer">Yes — but parental consent documentation required.</div>
            </div>

            <div class="faq-item">
                <div class="faq-question">30. Can I apply visa without employment?</div>
                <div class="faq-answer">Yes — but must prove strong alternative financial support.</div>
            </div>

            <div class="faq-item">
                <div class="faq-question">31. Do you assist asylum, illegal entry or refugee applications?</div>
                <div class="faq-answer">No — we only support legal travel entry processes.</div>
            </div>

            <div class="faq-item">
                <div class="faq-question">32. Do embassies do interviews?</div>
                <div class="faq-answer">Some countries do — we provide interview coaching and question preparation.</div>
            </div>

            <div class="faq-item">
                <div class="faq-question">33. How early should I apply before travel?</div>
                <div class="faq-answer">Minimum 2–6 months ahead recommended.</div>
            </div>

            <div class="faq-item">
                <div class="faq-question">34. Do you help with document translation?</div>
                <div class="faq-answer">Yes — we support embassy approved translation formats.</div>
            </div>

            <div class="faq-item">
                <div class="faq-question">35. Are your services legal?</div>
                <div class="faq-answer">Yes — M25 operates as a professional legal travel consulting agency.</div>
            </div>
        </div>

        <!-- Data Privacy -->
        <div class="faq-section">
            <h3><i class="fas fa-shield-alt me-2"></i>Data Privacy</h3>
            
            <div class="faq-item">
                <div class="faq-question">36. Is my information safe?</div>
                <div class="faq-answer">Yes. We do not share client data to third parties without consent.</div>
            </div>

            <div class="faq-item">
                <div class="faq-question">37. Do you keep my documents after process?</div>
                <div class="faq-answer">Documents kept only during active case then deleted unless client requests storage.</div>
            </div>
        </div>

        <!-- Specific Travel Questions -->
        <div class="faq-section">
            <h3><i class="fas fa-globe-americas me-2"></i>Specific Travel Questions</h3>
            
            <div class="faq-item">
                <div class="faq-question">38. Can I travel while my visa is still processing?</div>
                <div class="faq-answer">No — wait until visa is approved.</div>
            </div>

            <div class="faq-item">
                <div class="faq-question">39. Is travel history important?</div>
                <div class="faq-answer">Useful but not mandatory for many visas.</div>
            </div>

            <div class="faq-item">
                <div class="faq-question">40. Which country is easiest to get visa?</div>
                <div class="faq-answer">No country is easy or guaranteed — depends on profile compliance.</div>
            </div>

            <div class="faq-item">
                <div class="faq-question">41. Do you assist investor visas?</div>
                <div class="faq-answer">Yes — select global investor residency program support.</div>
            </div>

            <div class="faq-item">
                <div class="faq-question">42. Can you assist church travel / religious travel tours?</div>
                <div class="faq-answer">Yes — pilgrimages, conference visits and religious events.</div>
            </div>

            <div class="faq-item">
                <div class="faq-question">43. Do you help with festival / holiday travel planning?</div>
                <div class="faq-answer">Yes — we create full holiday itineraries.</div>
            </div>
        </div>

        <!-- Final Contact Section -->
        <div class="faq-section">
            <h3><i class="fas fa-phone me-2"></i>Contact & Getting Started</h3>
            
            <div class="faq-item">
                <div class="faq-question">44. How do I start application process with M25?</div>
                <div class="faq-answer">Contact us first for screening & consultation.</div>
            </div>

            <div class="faq-item">
                <div class="faq-question">45. What communication channels does M25 support globally?</div>
                <div class="faq-answer">WhatsApp / Email / Website Live Enquiry / Phone.</div>
            </div>

            <div class="faq-item">
                <div class="faq-question">46. How quickly do you respond?</div>
                <div class="faq-answer">Same day or within 24hrs depending on time zone.</div>
            </div>

            <div class="faq-item">
                <div class="faq-question">47. Can I schedule video consultation?</div>
                <div class="faq-answer">Yes — Zoom, Google Meet or WhatsApp Video.</div>
            </div>

            <div class="faq-item">
                <div class="faq-question">48. Do you assist multiple country applications same year?</div>
                <div class="faq-answer">Yes — we guide travel strategy.</div>
            </div>

            <div class="faq-item">
                <div class="faq-question">49. Can M25 recommend safe realistic countries for first-time travelers?</div>
                <div class="faq-answer">Yes — based on your personal travel profile.</div>
            </div>

            <div class="faq-item">
                <div class="faq-question">50. Why choose M25 Travel & Tour?</div>
                <div class="faq-answer">Professional review, accurate presentation, honest assessment, global support and legal processing advisory from start to end.</div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="cta-section">
            <h3>Ready to Begin Your Visa Process?</h3>
            <p>Click to Start — Get Consultation + Profile Assessment Today.</p>
            <a href="contact.php" class="btn-cta">
                <i class="fas fa-rocket me-2"></i>Start Your Application
            </a>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>M25 Travel & Tour</h5>
                    <p>Your trusted global visa and travel partner</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p>&copy; 2024 M25 Travel & Tour. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function searchFAQ() {
            const searchTerm = document.getElementById('faqSearch').value.toLowerCase();
            const faqItems = document.querySelectorAll('.faq-item');
            
            faqItems.forEach(item => {
                const question = item.querySelector('.faq-question').textContent.toLowerCase();
                const answer = item.querySelector('.faq-answer').textContent.toLowerCase();
                
                if (question.includes(searchTerm) || answer.includes(searchTerm)) {
                    item.style.display = 'block';
                    // Highlight search terms
                    if (searchTerm) {
                        highlightText(item, searchTerm);
                    }
                } else {
                    item.style.display = 'none';
                }
            });
        }

        function highlightText(element, searchTerm) {
            const walker = document.createTreeWalker(
                element,
                NodeFilter.SHOW_TEXT,
                null,
                false
            );

            const textNodes = [];
            let node;
            while (node = walker.nextNode()) {
                textNodes.push(node);
            }

            textNodes.forEach(textNode => {
                const parent = textNode.parentNode;
                if (parent.tagName !== 'SCRIPT' && parent.tagName !== 'STYLE') {
                    const text = textNode.textContent;
                    const regex = new RegExp(`(${searchTerm})`, 'gi');
                    if (regex.test(text)) {
                        const highlightedText = text.replace(regex, '<span class="highlight">$1</span>');
                        const wrapper = document.createElement('div');
                        wrapper.innerHTML = highlightedText;
                        parent.replaceChild(wrapper, textNode);
                        wrapper.outerHTML = wrapper.innerHTML;
                    }
                }
            });
        }

        // Search on Enter key
        document.getElementById('faqSearch').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchFAQ();
            }
        });

        // Clear search
        document.getElementById('faqSearch').addEventListener('input', function() {
            if (this.value === '') {
                const faqItems = document.querySelectorAll('.faq-item');
                faqItems.forEach(item => {
                    item.style.display = 'block';
                    // Remove highlights
                    const highlights = item.querySelectorAll('.highlight');
                    highlights.forEach(highlight => {
                        highlight.outerHTML = highlight.innerHTML;
                    });
                });
            }
        });
    </script>
</body>
</html>
