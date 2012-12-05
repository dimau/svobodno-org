<div class="simpleBlockForAnyContent">
	<?php include "templates/adminTemplates/templ_adminUserItem.php";?>

	<hr>

	<div style="margin-left: 40px;">
		<?php
		foreach ($allProperties as $property) {
			if ($property['userId'] == $user['id']) {
				include "templates/adminTemplates/templ_adminPropertyItem.php";
			}
		}
		?>
    </div>

    <hr>

    <div style="margin-left: 40px;">
		<?php
		foreach ($allRequestsToView as $requestToView) {
			if ($requestToView['tenantId'] == $user['id']) {
				include "templates/adminTemplates/templ_adminRequestToViewItem.php";
			}
		}
		?>
	</div>
</div>