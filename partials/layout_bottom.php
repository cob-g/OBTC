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

</body>
</html>
