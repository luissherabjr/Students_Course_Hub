<?php
$pageTitle = "Home - Student Course Hub";
require_once '../includes/config.php';
include '../includes/header.php';
?>

<div class="hero-section">
    <div class="container">
        <h1>Discover Your Future at University of Knowledge</h1>
        <p>Explore our wide range of undergraduate and postgraduate programmes</p>
        <div class="search-box">
            <form action="../programmes.php" method="GET" role="search">
                <label for="search" class="sr-only">Search programmes</label>
                <input type="text" 
                       id="search" 
                       name="search" 
                       placeholder="Search for programmes (e.g., Cyber Security)" 
                       class="search-input">
                <button type="submit" class="search-button">Search</button>
            </form>
        </div>
    </div>
</div>

<section class="featured-programmes">
    <div class="container">
        <h2>Featured Programmes</h2>
        
        <div class="programme-filters">
            <button class="filter-btn active" data-filter="all">All</button>
            <button class="filter-btn" data-filter="undergraduate">Undergraduate</button>
            <button class="filter-btn" data-filter="postgraduate">Postgraduate</button>
        </div>
        
        <div class="programme-grid">
            <!-- Undergraduate Programme 1 -->
            <div class="programme-card" data-level="undergraduate">
                <div class="programme-image">
                    <img src="../assets/images/placeholder.jpg" alt="Computer Science programme">
                </div>
                <div class="programme-content">
                    <span class="programme-level undergraduate">Undergraduate</span>
                    <h3><a href="../programme-detail.php?id=1">BSc Computer Science</a></h3>
                    <p class="programme-description">Study the fundamentals of computing, programming, and software development.</p>
                    <div class="programme-meta">
                        <span>3 years</span>
                        <span>Full-time</span>
                    </div>
                    <a href="../programme-detail.php?id=1" class="btn btn-primary">View Programme</a>
                </div>
            </div>
            
            <!-- Undergraduate Programme 2 -->
            <div class="programme-card" data-level="undergraduate">
                <div class="programme-image">
                    <img src="../assets/images/placeholder.jpg" alt="Cyber Security programme">
                </div>
                <div class="programme-content">
                    <span class="programme-level undergraduate">Undergraduate</span>
                    <h3><a href="../programme-detail.php?id=2">BSc Cyber Security</a></h3>
                    <p class="programme-description">Learn to protect systems, networks, and data from digital attacks.</p>
                    <div class="programme-meta">
                        <span>3 years</span>
                        <span>Full-time</span>
                    </div>
                    <a href="../programme-detail.php?id=2" class="btn btn-primary">View Programme</a>
                </div>
            </div>
            
            <!-- Postgraduate Programme -->
            <div class="programme-card" data-level="postgraduate">
                <div class="programme-image">
                    <img src="../assets/images/placeholder.jpg" alt="Data Science programme">
                </div>
                <div class="programme-content">
                    <span class="programme-level postgraduate">Postgraduate</span>
                    <h3><a href="../programme-detail.php?id=3">MSc Data Science</a></h3>
                    <p class="programme-description">Advanced study in data analysis, machine learning, and big data technologies.</p>
                    <div class="programme-meta">
                        <span>1 year</span>
                        <span>Full-time</span>
                    </div>
                    <a href="../programme-detail.php?id=3" class="btn btn-primary">View Programme</a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="why-choose-us">
    <div class="container">
        <h2>Why Study With Us?</h2>
        <div class="features-grid">
            <div class="feature">
                <h3>Expert Faculty</h3>
                <p>Learn from leading researchers and industry professionals</p>
            </div>
            <div class="feature">
                <h3>Modern Facilities</h3>
                <p>Access state-of-the-art labs and learning resources</p>
            </div>
            <div class="feature">
                <h3>Industry Connections</h3>
                <p>Strong links with employers for work placements</p>
            </div>
            <div class="feature">
                <h3>Student Support</h3>
                <p>Comprehensive academic and personal support services</p>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>