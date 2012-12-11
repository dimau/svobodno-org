<div class='realtyObject' propertyId='{propertyId}'>
    <div class="serviceMarks">
        <div class='numberOfRealtyObject'>{number}</div>
        <span class='{actionFavorites} aloneStar' propertyId='{propertyId}'><img src='{imgFavorites}'></span>
    </div>
    <div class="overFotosWrapper">
        {fotosWrapper}
    </div>
    <div class="mainContent">
        <ul class='listDescriptionSmall'>
            <li>
                <span class='headOfString'>{typeOfObject}</span> {address}
            </li>
            <li>
                <span class='headOfString'>{costOfRentingName}</span> {costOfRenting} {currency} {utilities}
            </li>
            <li>
                <span class='headOfString'>{amountOfRoomsName}</span> {amountOfRooms}{adjacentRooms}
            </li>
            <li>
                <span class='headOfString'>{areaValuesName}</span> {areaValues} {areaValuesMeasure}
            </li>
            <li>
                <span class='headOfString'>{floorName}</span> {floor}
            </li>
        </ul>
        <div class='advertActions'>
            <a href='property.php?propertyId={propertyId}' target='_blank'>подробнее</a>
        </div>
        <div class="clearBoth"></div>
    </div>
    <div class="clearBoth"></div>
</div>