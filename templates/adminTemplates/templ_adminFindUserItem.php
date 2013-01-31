<div class="mainContentBlock">
    <?php require $_SERVER['DOCUMENT_ROOT'] . "/templates/adminTemplates/templ_adminUserItem.php";?>

    <hr>

    <div style="margin-left: 40px;">
        <?php
        foreach ($allProperties as $propertyCharacteristic) {
            if ($propertyCharacteristic['userId'] == $userCharacteristic['id']) {
                require $_SERVER['DOCUMENT_ROOT'] . "/templates/adminTemplates/templ_adminPropertyItem.php";
            }
        }
        ?>
    </div>

</div>