<div class='balloonBlock' coordX='{coordX}' coordY='{coordY}' propertyId='{propertyId}'>
    <div class="ballonBody">
        <div class="costOfRenting">
            <div>
                <span class="costOfRentingString">
                    {costOfRenting}
                </span>
                <span class="unimportantText">
                    {currency}
                </span>
            </div>
            <div class="unimportantText">
                {utilities}
            </div>
        </div>
        <div class="address">
            <div class="addressString">
                <a href='property.php?propertyId={propertyId}' target='_blank'>{address}</a>
            </div>
            <div>
                <span class="unimportantText">{typeOfObject} / {district}</span>
            </div>
        </div>
        <div class="secondaryOptionsBlock">
            <div class="secondaryOption">
                <span class="unimportantText">{amountOfRoomsName}</span> {amountOfRooms}{adjacentRooms}
            </div>
            <div class="secondaryOption">
                <span class="unimportantText">Площадь:</span>
                <span>{areaValues} {areaValuesMeasure}</span>
            </div>
            <div class="secondaryOption">
                <span class="unimportantText">{floorName}</span> {floor}
            </div>
        </div>
        <div class="balloonAdvertActions">
            <div style="float: left;">
                {favorites}
            </div>
            {hasPhotos}
            <a href='property.php?propertyId={propertyId}' target='_blank'>подробнее</a>
        </div>
    </div>
</div>