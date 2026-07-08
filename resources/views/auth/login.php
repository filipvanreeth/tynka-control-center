<?php echo $this->render('header'); ?>
<div class="full-screen bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="container mx-auto p-4 max-w-md">
        <div class="my-6 text-center">
            <h1 class="text-periwinkle-100 uppercase">
                <span class="font-bold">Tynka's</span> Care Center
            </h1>
        </div>

        <?php if ($flash !== null && $flash['type'] === 'error') { ?>
            <div class="bg-red-100 text-red-800 p-3 rounded-lg mb-4" role="alert">
                <?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php } ?>

        <form method="post" action="<?= htmlspecialchars($appUrl, ENT_QUOTES, 'UTF-8') ?>/login" class="bg-white p-6 rounded-lg shadow">
            <label class="block mb-4">
                <span class="block mb-1 font-bold">Email</span>
                <input type="email" name="email" required autofocus autocomplete="username"
                       class="w-full border border-gray-300 rounded-lg p-2">
            </label>
            <label class="block mb-4">
                <span class="block mb-1 font-bold">Password</span>
                <input type="password" name="password" required autocomplete="current-password"
                       class="w-full border border-gray-300 rounded-lg p-2">
            </label>
            <button type="submit" class="bg-opal-800 text-opal-50 font-bold p-3 rounded-lg w-full">
                Sign in
            </button>
        </form>
    </div>
</div>
<?php echo $this->render('footer'); ?>
