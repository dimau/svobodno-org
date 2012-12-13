<div class="simpleBlockForAnyContent">
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

    <hr>

    <div style="margin-left: 40px;">
		<?php
		foreach ($allRequestsToView as $requestToView) {
			if ($requestToView['tenantId'] == $userCharacteristic['id']) {
				require $_SERVER['DOCUMENT_ROOT'] . "/templates/adminTemplates/templ_adminRequestToViewItem.php";
			}
		}
		?>
	</div>
</div>