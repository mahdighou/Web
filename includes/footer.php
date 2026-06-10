    </main>
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>درباره رستوران برتر</h3>
                    <p>رستوران برتر با بیش از ۱۰ سال سابقه در ارائه باکیفیت‌ترین غذاهای ایرانی و بین‌المللی، همواره در تلاش است تا لبخند رضایت را بر لبان شما بنشاند.</p>
                </div>
                <div class="footer-section">
                    <h3>دسترسی سریع</h3>
                    <ul>
                        <li><a href="<?php echo BASE_URL; ?>">صفحه اصلی</a></li>
                        <li><a href="<?php echo BASE_URL; ?>register.php">عضویت در سایت</a></li>
                        <li><a href="<?php echo BASE_URL; ?>cart.php">پیگیری سفارش</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>تماس با ما</h3>
                    <p>آدرس: تهران، خیابان ولیعصر، نرسیده به میدان ونک</p>
                    <p>تلفن: ۰۲۱-۱۲۳۴۵۶۷۸</p>
                    <div class="social-links" style="margin-top: 15px;">
                        <a href="#">IG</a>
                        <a href="#">TG</a>
                        <a href="#">WA</a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>تمامی حقوق این وب‌سایت محفوظ است &copy; <?php echo date('Y'); ?> | طراحی شده برای پروژه پایانی</p>
            </div>
        </div>
    </footer>

    <?php if (isset($_SESSION['user_id'])): ?>
    <!-- Chat Widget -->
    <div id="chat-btn" onclick="toggleChat()">
        <span style="color: #fff; font-size: 24px;">💬</span>
        <div class="badge" id="chat-badge"></div>
    </div>

    <div id="chat-window">
        <div id="chat-header">
            <span id="chat-title">پشتیبانی آنلاین</span>
            <span onclick="toggleChat()" style="cursor: pointer;">✖</span>
        </div>
        <div id="chat-body" style="display: flex; flex-direction: column;">
            <!-- Messages or User List will load here -->
        </div>
        <div id="chat-footer" style="display: none;">
            <input type="text" id="chat-input" placeholder="پیام خود را بنویسید...">
            <button onclick="sendMessage()" class="btn" style="padding: 5px 15px;">ارسال</button>
        </div>
    </div>

    <script>
        const CURRENT_USER_ID = <?php echo $_SESSION['user_id']; ?>;
        const IS_ADMIN = <?php echo $_SESSION['role'] == 'admin' ? 'true' : 'false'; ?>;
        const BASE_URL = '<?php echo BASE_URL; ?>';
    </script>
    <script src="<?php echo BASE_URL; ?>assets/js/chat.js"></script>
    <?php endif; ?>
</body>
</html>
