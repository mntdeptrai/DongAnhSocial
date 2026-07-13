<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc><?php echo e(url('/')); ?></loc>
        <lastmod><?php echo e(now()->tz('UTC')->toAtomString()); ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc><?php echo e(url('/tim-kiem')); ?></loc>
        <lastmod><?php echo e(now()->tz('UTC')->toAtomString()); ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    <?php $__currentLoopData = $eateries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $eatery): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <url>
            <loc><?php echo e(url('/dia-diem/' . $eatery->slug)); ?></loc>
            <lastmod><?php echo e($eatery->updated_at->tz('UTC')->toAtomString()); ?></lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.9</priority>
        </url>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</urlset>
<?php /**PATH /www/wwwroot/donganhdiscovery.xadonganh.com/FOOD_MAP/resources/views/sitemap.blade.php ENDPATH**/ ?>