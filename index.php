<?php
require __DIR__ . '/db.php';
$movies = $pdo->query('SELECT * FROM movies ORDER BY created_at DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">



<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Movie Ticket Booking</title>
  <link rel="stylesheet" href="assets/styles.css">
</head>

<body>
  <header>
    <img src="logo/movietix.png" alt="Movietix" width="145" height="65" />
    <nav>
      <?php if (user()): ?>
        <span class="muted">Hi, <?= h(user()['name']) ?> (<?= h(user()['role']) ?>)</span>
        <?php if (isAdmin()): ?>
          <a class="link" href="/movietix/admin/movies.php">Admin</a>
          <a class="link" href="/movietix/admin/showtimes.php">Showtimes</a>
        <?php endif; ?>
        <a class="link" href="/movietix/auth/logout.php">Logout</a>
      <?php else: ?>
        <a class="link" href="/movietix/auth/login.php">Login</a>
        <a class="link" href="/movietix/auth/register.php">Register</a>
      <?php endif; ?>
    </nav>
  </header>

  <!-- Hero section -->
  <section class="hero">
    <div class="hero-slider">
      <div class="slide active" style="background-image:url('sliders/slider1.jpg')"></div>
      <div class="slide" style="background-image:url('sliders/slider2.jpg')"></div>
      <div class="slide" style="background-image:url('sliders/slider3.jpg')"></div>
    </div>

    <div class="hero-content">
      <h2>Welcome to Movietix</h2>
      <p>Book your favorite movies instantly and enjoy the show!</p>
    </div>

    <!-- Arrows -->
    <button class="arrow left">&#10094;</button>
    <button class="arrow right">&#10095;</button>

    <!-- Dots -->
    <div class="dots">
      <span class="dot active"></span>
      <span class="dot"></span>
      <span class="dot"></span>
    </div>
  </section>


  <main class="container">
    <section class="movie-section">
      <h2>🎥 Now Showing</h2>
      <div class="movie-carousel">
        <?php foreach ($movies as $m): ?>
          <article class="movie-card">
            <div class="movie-poster" style="background-image: url('<?= h($m['poster_url']) ?>');">
              <div class="overlay">
                <div class="movie-details">
                  <h3><?= h($m['title']) ?></h3>
                  <p>⌛ <?= h($m['rating'] ?? 'Unknown') ?></p>
                  <p>⏱ <?= (int) $m['duration_min'] ?> mins</p>
                  <p>🌍 <?= h($m['language'] ?? 'ENG') ?></p>
                </div>
                <a class="btn-buy" href="movie.php?id=<?= (int) $m['id'] ?>">BUY NOW</a>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
        <?php if (!$movies): ?>
          <p>No movies yet. Add some in the database.</p>
        <?php endif; ?>
      </div>
    </section>

    <section class="movie-section">
      <h2>🍿 Coming Soon</h2>
      <div class="movie-carousel">
        <div class="movie-card placeholder">
          <p>Stay tuned for upcoming releases!</p>
        </div>
      </div>
    </section>
  </main>

  <footer class="footer">© <?= date('Y') ?> Movietix</footer>

  <!-- Slider script -->
  <script>
    const slides = document.querySelectorAll('.hero-slider .slide');
    const dots = document.querySelectorAll('.dot');
    let current = 0;
    let timer;

    function showSlide(index) {
      slides.forEach((s, i) => s.classList.toggle('active', i === index));
      dots.forEach((d, i) => d.classList.toggle('active', i === index));
      current = index;
    }

    function nextSlide() {
      let next = (current + 1) % slides.length;
      showSlide(next);
    }

    function prevSlide() {
      let prev = (current - 1 + slides.length) % slides.length;
      showSlide(prev);
    }

    document.querySelector('.arrow.right').addEventListener('click', () => {
      nextSlide();
      resetTimer();
    });

    document.querySelector('.arrow.left').addEventListener('click', () => {
      prevSlide();
      resetTimer();
    });

    dots.forEach((dot, i) => {
      dot.addEventListener('click', () => {
        showSlide(i);
        resetTimer();
      });
    });

    function startTimer() {
      timer = setInterval(nextSlide, 5000);
    }

    function resetTimer() {
      clearInterval(timer);
      startTimer();
    }

    startTimer();
  </script>

</body>

</html>