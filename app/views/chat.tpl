<?php $this->widget('Search');?>
<?php $this->widget('Notification');?>
<?php $this->widget('VisioLink');?>
<?php $this->widget('Stickers');?>
<?php $this->widget('Notifications');?>
<?php $this->widget('Snap');?>

<nav class="color dark">
    <?php $this->widget('Presence');?>
    <?php $this->widget('Navigation');?>
</nav>

<?php $this->widget('BottomNavigation');?>

<main>
    <?php $this->widget('Upload');?>
    <?php $this->widget('Chat');?>
    <div>
        <?php $this->widget('Chats');?>
        <?php $this->widget('Rooms');?>
    </div>
</main>
