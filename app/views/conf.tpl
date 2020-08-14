<?php $this->widget('Search');?>
<?php $this->widget('VisioLink');?>
<?php $this->widget('Notification');?>
<?php $this->widget('Toast');?>
<?php $this->widget('Onboarding');?>
<?php $this->widget('Notifications');?>

<nav class="color dark">
    <?php $this->widget('Presence');?>
    <?php $this->widget('Navigation');?>
</nav>

<?php $this->widget('BottomNavigation');?>

<main>
    <div>
        <header>
            <ul class="list middle">
                <li>
                    <span id="menu" class="primary icon active gray" >
                        <i class="material-icons">settings</i>
                    </span>
                    <div>
                        <p><?php echo __('page.configuration'); ?></p>
                    </div>
                </li>
            </ul>
        </header>

        <?php $this->widget('Tabs');?>
        <?php $this->widget('Vcard4');?>
        <?php if (\App\User::me()->hasPubsub()) { ?>
            <?php $this->widget('Avatar');?>
            <?php $this->widget('Config');?>
        <?php } ?>
        <?php $this->widget('Account');?>
        <?php $this->widget('AdHoc');?>
    </div>
</main>
