<?php echo $__env->make("layout.header", array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
<?php echo $__env->yieldContent("content"); ?>
<?php echo $__env->make("layout.footer", array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>