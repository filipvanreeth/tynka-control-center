<?php echo $this->render('header'); ?>
<div class="w-screen bg-gray-100 min-h-screen pb-8 flex items-center justify-center">
    <div class="flex flex-col">
        <?php echo $this->render('components/check-in-form', [
            'form' => $form,
        ]); ?>

        <!-- Aparte form voor delete -->
        <form action="/checkin/fff/delete" method="POST"
              onsubmit="return confirm('Are you sure you want to delete this check-in?')">
            <button type="submit" class="bg-blue-600 text-red-500 font-bold">Delete</button>
        </form>
    </div>
</div>
<?php echo $this->render('footer'); ?>
