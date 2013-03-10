<div class='balloonBlock' coordX='{coordX}' coordY='{coordY}' propertyId='{propertyId}'>
    <div class="ballonBody">
        <div class="address">
            <span class="unimportantText">{typeOfObject}</span> <span class="addressString"><a href='property.php?propertyId={propertyId}'
                                                                   target='_blank'>{address}</a></span>
        </div>
        <div class="costOfRenting">
            <span class="costOfRentingString">{costOfRenting}</span> <span class="unimportantText">{currency}</span>
            <span class="unimportantText">{utilities}</span>
        </div>
        <div class="secondaryOptionsBlock">
            <div class="secondaryOption">
                <span class="unimportantText">{amountOfRoomsName}</span> {amountOfRooms}{adjacentRooms}
            </div>
            <div class="secondaryOption">
                <span class="unimportantText">Площадь:</span> {areaValues} {areaValuesMeasure}
            </div>
            <div class="secondaryOption">
                <span class="unimportantText">{floorName}</span> {floor}
            </div>
        </div>
        <div style='width:100%; font-size: 12px;'>
            <a href='property.php?propertyId={propertyId}' target='_blank'>подробнее</a>
            {hasPhotos}
            <div style='float: right;'>
                {favorites}
            </div>
        </div>
    </div>
</div>