<!-- aboutUs.php -->
<?php // Include header
$includeLoader = true;
include 'header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>About Us | CompareIt</title>
<style>
     .retailers-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
      gap: 20px;
      padding: 10px 0;
    }
    .retailer-card {
      display: flex;
      flex-direction: column;
      align-items: center;
    }
    .retailer-card img {
      width: 100px;
      height: auto;
      border-radius: 6px;
    }
    .retailer-card span {
      margin-top: 8px;
      font-weight: bold;
    }
</style>
</head>
<body>

  <section>
    <h1>About CompareIt</h1>
    <h2>The Problem: Information Overload</h2>
    <p>
      Choosing the right product or service can feel overwhelming. With new options appearing daily, it's difficult to find reliable, unbiased information that helps you decide. CompareIt exists to solve this problem by simplifying complex choices through clear, factual comparisons.
    </p>
  </section>

  <section>
    <h2>Our Solution: Fact-Based, Objective Comparisons</h2>
    <p>
      CompareIt is a price comparison web application designed to help users make informed shopping decisions by comparing real-time prices of products from multiple online and physical retailers.
    </p>
  </section>

  <section>
    <h2>Our Values</h2>
    <ul>
      <li><strong>Accuracy</strong>: We verify everything.</li>
      <li><strong>Impartiality</strong>: No paid influence — rankings are merit-based.</li>
      <li><strong>Transparency</strong>: We show how and why items are scored.</li>
      <li><strong>Simplicity</strong>: Clear, user-friendly designs.</li>
      <li><strong>Informativeness</strong>: Only the most relevant specs and facts.</li>
    </ul>
  </section>

  <section>
    <h2>A Little Bit of History</h2>
    <p>
      CompareIt was founded to help people make smarter decisions through unbiased data. Starting with tech comparisons, we’ve expanded to include everything from apps to cities. With millions of comparisons available in multiple languages, our platform empowers people across the globe.
    </p>
  </section>

  <section>
    <h2>Meet Our Team</h2>
    <p>We are a passionate team of engineers, designers, analysts, and editors committed to building the most trusted comparison tool on the web. If you’re interested in joining us, email <a href="mailto:careers@compareit.com">careers@compareit.com</a>.</p>
  </section>

  <!-- Retailers Section -->
  <section class="retailers-section">
    <h2>Our Retail Partners</h2>
    <div class="retailers-grid">
      <div class="retailer-card">
        <img src="https://th.bing.com/th/id/OIP.douAQqLQCydHXDqsPfOcpwHaEK?cb=iwp2&rs=1&pid=ImgDetMain" alt="Amazon" />
        <span>Amazon</span>
      </div>
      <div class="retailer-card">
        <img src="https://play-lh.googleusercontent.com/Zy-BRdWCKBqJeDCcoFnrEoGeCqQnSEfs3qBaC7_nWHLMm3-Nfs1HRul7cBJqjLiDH2h4" alt="Takealot" />
        <span>Takealot</span>
      </div>
      <div class="retailer-card">
        <img src="https://d1ralsognjng37.cloudfront.net/95c00978-d77b-4770-8dab-9bfa14427de1.jpeg" alt="Game" />
        <span>Game</span>
      </div>
      <div class="retailer-card">
        <img src="https://businesstech.co.za/news/wp-content/uploads/2019/11/Makro-Black-Friday.png" alt="Makro" />
        <span>Makro</span>
      </div>
      <div class="retailer-card">
        <img src="https://mybroadband.co.za/news/wp-content/uploads/2013/09/Incredible-Connection.jpg" alt="Incredible Connection" />
        <span>Incredible Connection</span>
      </div>
      <div class="retailer-card">
        <img src="https://th.bing.com/th/id/OIP.ZCicsG7p-1C_ryChksaslQHaHa?cb=iwp2&rs=1&pid=ImgDetMain" alt="iStore" />
        <span>iStore</span>
      </div>
      <div class="retailer-card">
        <img src="https://th.bing.com/th/id/OIP.z3QQ-ne-bxIdUQpwLtnzhwHaHV?cb=iwp2&rs=1&pid=ImgDetMain" alt="MTN" />
        <span>MTN</span>
      </div>
      <div class="retailer-card">
        <img src="https://th.bing.com/th/id/OIP.GKAiOtzC1JA4SVNPllLREQAAAA?cb=iwp2&rs=1&pid=ImgDetMain" alt="Vodacom" />
        <span>Vodacom</span>
      </div>
    </div>
  </section>

 
</body>
</html>
<?php include 'footer.php'; ?>