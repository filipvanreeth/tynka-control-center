<?php
/** @var \TynkaControlCenter\Entities\CheckInEntity $checkIn */

$handler = $handlerService->getHandler($checkIn->getHandler());

$handlerAvatar = !empty($handler['avatar'])
    ? $this->appConfig->appUrl . '/assets/images/' . $handler['avatar']
    : null;

$handlerName = htmlspecialchars($handlerService->getHandlerName($checkIn->getHandler()), ENT_QUOTES, 'UTF-8');

$createdAtDate = $checkIn->getCreatedAt()
    ->setTimezone(new \DateTimeZone('Europe/Brussels'));

$today = new \DateTime('now', new \DateTimeZone('Europe/Brussels'));
$yesterday = new \DateTime('yesterday', new \DateTimeZone('Europe/Brussels'));
$dateColor = 'text-gray-500';

if ($createdAtDate->format('Y-m-d') === $today->format('Y-m-d')) {
    $createdAt = 'Today - ' . $createdAtDate->format('H:i');
    $dateColor = 'text-opal-500';
} elseif ($createdAtDate->format('Y-m-d') === $yesterday->format('Y-m-d')) {
    $createdAt = 'Yesterday - ' . $createdAtDate->format('H:i');
    $dateColor = 'text-periwinkle-500';
} else {
    $createdAt = $createdAtDate->format('j F Y - H:i');
}

$createdAt = htmlspecialchars($createdAt, ENT_QUOTES, 'UTF-8');
$dateColor = htmlspecialchars($dateColor, ENT_QUOTES, 'UTF-8');

$hasPeed = $checkIn->hasPeed();
$hasPooped = $checkIn->hasPooped();
$hadFood = $checkIn->hadFood();
$hadSnack = $checkIn->hadSnack();
?>

<div class="rounded-xl bg-white p-4 first-of-type:border-2 first-of-type:border-opal-800 first-of-type:bg-opal-950/50">
    <div class="flex items-center">
        <div class="shrink-0 mr-4">
            <img src="<?= $handlerAvatar ?>" class="bg-gray-200 border-none rounded-xl w-12 h-12">
        </div>
        <div class="flex-auto">
            <p class="<?= $dateColor ?> text-sm"><?= $createdAt ?></p>
            <h3 class="font-bold text-lg"><?= $handlerName ?></h3>
        </div>
        <div
            class="flex-auto flex justify-end gap-1.5">
            <?php if ($hasPooped): ?>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-black w-7 h-7">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M10 12h.01"/>
                    <path d="M14 12h.01"/>
                    <path d="M10 16a3.5 3.5 0 0 0 4 0"/>
                    <path d="M11 4c2 0 3.5 1.5 3.5 4l.164 0a2.5 2.5 0 0 1 2.196 3.32a3 3 0 0 1 1.615 3.063a3 3 0 0 1 -1.299 5.607l-.176 0h-10a3 3 0 0 1 -1.474 -5.613a3 3 0 0 1 1.615 -3.062a2.5 2.5 0 0 1 2.195 -3.32l.164 0c1.5 0 2.5 -2 1.5 -4l0 .005"/>
                </svg>
            <?php endif ?>
            <?php if ($hasPeed): ?>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewbox="0 0 24 24" fill="none" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-black w-7 h-7"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4.072 20.3a2.999 2.999 0 0 0 3.856 0a3.002 3.002 0 0 0 .67 -3.798l-2.095 -3.227a.6 .6 0 0 0 -1.005 0l-2.098 3.227a3.003 3.003 0 0 0 .671 3.798"/><path d="M16.072 20.3a2.999 2.999 0 0 0 3.856 0a3.002 3.002 0 0 0 .67 -3.798l-2.095 -3.227a.6 .6 0 0 0 -1.005 0l-2.098 3.227a3.003 3.003 0 0 0 .671 3.798"/><path d="M10.072 10.3a2.999 2.999 0 0 0 3.856 0a3.002 3.002 0 0 0 .67 -3.798l-2.095 -3.227a.6 .6 0 0 0 -1.005 0l-2.098 3.227a3.003 3.003 0 0 0 .671 3.798l.001 0"/></svg>
            <?php endif ?>
            <?php if ($hadFood): ?>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewbox="0 0 24 24" fill="none" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-black w-7 h-7">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M10 15l5.586 -5.585a2 2 0 1 1 3.414 -1.415a2 2 0 1 1 -1.413 3.414l-3.587 3.586"/>
                    <path d="M12 13l-3.586 -3.585a2 2 0 1 0 -3.414 -1.415a2 2 0 1 0 1.413 3.414l3.587 3.586"/>
                    <path d="M3 20h18c-.175 -1.671 -.046 -3.345 -2 -5h-14c-1.333 1 -2 2.667 -2 5"/>
                </svg>
            <?php endif ?>
            <?php if ($hadSnack): ?>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-black w-7 h-7"><path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M15 3a3 3 0 0 1 3 3a3 3 0 1 1 -2.12 5.122l-4.758 4.758a3 3 0 1 1 -5.117 2.297l0 -.177l-.176 0a3 3 0 1 1 2.298 -5.115l4.758 -4.758a3 3 0 0 1 2.12 -5.122l-.005 -.005" /></svg>
            <?php endif ?>
        </div>
    </div>
</div>

