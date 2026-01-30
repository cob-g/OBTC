<?php
$user = auth_user();
$role = $user ? $user['role'] : null;
?>

<?php if ($role === 'admin' && $user): ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
function toggleUserDropdown() {
    const dropdown = document.getElementById('userDropdown');
    dropdown.classList.toggle('hidden');
}

function toggleMobileMenu() {
    const menu = document.getElementById('mobileMenu');
    menu.classList.toggle('hidden');
    // Prevent body scroll when menu is open
    if (!menu.classList.contains('hidden')) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
}

function toggleAdminMobileMenu() {
    const menu = document.getElementById('adminMobileMenu');
    menu.classList.toggle('hidden');
    // Prevent body scroll when menu is open
    if (!menu.classList.contains('hidden')) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('userDropdown');
    const button = event.target.closest('button[onclick="toggleUserDropdown()"]');
    
    if (dropdown && !dropdown.contains(event.target) && !button) {
        dropdown.classList.add('hidden');
    }
});
</script>

</body>
</html>
