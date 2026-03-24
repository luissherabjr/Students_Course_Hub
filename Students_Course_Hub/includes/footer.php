<?php
$base_url = '/students_course_hub/';
$current_year = date('Y');
?>
    </main> <!-- End of main content -->
    
    <!-- Footer -->
    <footer style="background: #2c3e50; color: #ecf0f1; margin-top: 50px; padding: 40px 0 20px;">
        <div style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
            
            <!-- Footer Content - 3 Simple Columns -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px; margin-bottom: 30px;">
                
                <!-- Column 1: Course Hub Info -->
                <div>
                    <h4 style="color: white; margin-bottom: 15px; font-size: 1.1rem;">
                        <i class="fas fa-graduation-cap" style="color: #667eea;"></i> Student Course Hub
                    </h4>
                    <p style="color: #bdc3c7; line-height: 1.6; font-size: 0.9rem;">
                        Your gateway to undergraduate and postgraduate programmes. Find your perfect course and start your journey today.
                    </p>
                </div>
                
                <!-- Column 2: Quick Links -->
                <div>
                    <h4 style="color: white; margin-bottom: 15px; font-size: 1.1rem;">
                        <i class="fas fa-link" style="color: #667eea;"></i> Quick Links
                    </h4>
                    <ul style="list-style: none; padding: 0;">
                        <li style="margin-bottom: 8px;">
                            <a href="<?php echo $base_url; ?>auth/login.php" style="color: #bdc3c7; text-decoration: none; font-size: 0.9rem; transition: color 0.3s;">
                                <i class="fas fa-chevron-right" style="color: #667eea; font-size: 0.7rem; margin-right: 8px;"></i> Admin/Staff Login
                            </a>
                        </li>
                        <li style="margin-bottom: 8px;">
                            <a href="#" style="color: #bdc3c7; text-decoration: none; font-size: 0.9rem; transition: color 0.3s;">
                                <i class="fas fa-chevron-right" style="color: #667eea; font-size: 0.7rem; margin-right: 8px;"></i> View Programmes
                            </a>
                        </li>
                        <li style="margin-bottom: 8px;">
                            <a href="#" style="color: #bdc3c7; text-decoration: none; font-size: 0.9rem; transition: color 0.3s;">
                                <i class="fas fa-chevron-right" style="color: #667eea; font-size: 0.7rem; margin-right: 8px;"></i> Staff Directory
                            </a>
                        </li>
                    </ul>
                </div>
                
                <!-- Column 3: Contact & Support -->
                <div>
                    <h4 style="color: white; margin-bottom: 15px; font-size: 1.1rem;">
                        <i class="fas fa-envelope" style="color: #667eea;"></i> Contact & Support
                    </h4>
                    <p style="margin-bottom: 8px; font-size: 0.9rem;">
                        <i class="fas fa-envelope" style="color: #667eea; width: 25px;"></i> 
                        <a href="mailto:coursehub@university.ac.uk" style="color: #bdc3c7; text-decoration: none;">coursehub@university.ac.uk</a>
                    </p>
                    <p style="margin-bottom: 8px; font-size: 0.9rem;">
                        <i class="fas fa-phone-alt" style="color: #667eea; width: 25px;"></i> 
                        +44 (0)20 1234 5678
                    </p>
                    <p style="font-size: 0.9rem;">
                        <i class="fas fa-clock" style="color: #667eea; width: 25px;"></i> 
                        Mon-Fri: 9am - 5pm
                    </p>
                </div>
            </div>
            
            <!-- Footer Bottom -->
            <div style="border-top: 1px solid #34495e; padding-top: 20px; text-align: center;">
                <div style="color: #95a5a6; font-size: 0.85rem;">
                    &copy; <?php echo $current_year; ?> Student Course Hub. All rights reserved.
                </div>
            </div>
        </div>
    </footer>
    
    <!-- JavaScript -->
    <script src="<?php echo $base_url; ?>js/script.js"></script>
    
    <!-- Simple Scroll to Top Button -->
    <button id="scrollToTop" style="position: fixed; bottom: 20px; right: 20px; background: #667eea; color: white; border: none; border-radius: 50%; width: 40px; height: 40px; cursor: pointer; display: none; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0,0,0,0.2); transition: all 0.3s; z-index: 99;">
        <i class="fas fa-arrow-up"></i>
    </button>
    
    <script>
        // Scroll to top functionality
        window.onscroll = function() {
            const button = document.getElementById('scrollToTop');
            if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
                button.style.display = 'flex';
            } else {
                button.style.display = 'none';
            }
        };
        
        document.getElementById('scrollToTop').addEventListener('click', function() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    </script>
</body>
</html>