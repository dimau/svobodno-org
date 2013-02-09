<div class="mainContentBlock">
    <?php require $websiteRoot . "/templates/adminTemplates/templ_adminUserItem.php";?>

    <hr>

    <div style="margin-left: 40px;">
        <?php
        foreach ($allProperties as $propertyCharacteristic) {
            if ($propertyCharacteristic['userId'] == $userCharacteristic['id']) {
                require $websiteRoot . "/templates/adminTemplates/templ_adminPropertyItem.php";
            }
        }
        ?>
    </div>

</div>