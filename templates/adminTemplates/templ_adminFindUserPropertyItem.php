<li>
	<?php echo $property['typeOfObject']; ?>:
	<span class="content">
		<?php
		echo $property['address'];
		if (isset($property['apartmentNumber']) && $property['apartmentNumber'] != "") {
			echo ", кв. № ".$property['apartmentNumber'];
		}
		?>
	</span>
</li>