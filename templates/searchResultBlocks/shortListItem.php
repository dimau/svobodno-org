<div class='realtyObject' propertyId='{propertyId}'>
    <div class="serviceMarks">
        <div class='numberOfRealtyObject'>{number}</div>
        <div>
            {favorites}
        </div>
        <div>
            {hasPhotos}
        </div>
    </div>
    <div class="mainContent">
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
                    <span class="unimportantText">{typeOfObject} / {district} / <span title="Дата публикации объявления">{reg_date}</span></span>
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
        <div class='advertActions'>
            <a href='property.php?propertyId={propertyId}' target='_blank'>подробнее</a>
        </div>
    </div>
</div>