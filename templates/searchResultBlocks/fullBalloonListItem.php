<div class='balloonBlock' coordX='{coordX}' coordY='{coordY}' propertyId='{propertyId}'>
    <div class='headOfBalloon'>{typeOfObject}{address}</div>
    {fotosWrapper}
    <ul class='listDescriptionSmall forBalloon'>
        <li>
            <span class='headOfString'>{costOfRentingName}</span> {costOfRenting} {currency}
        </li>
        <li>
            <span class='headOfString'>{utilitiesName}</span> {utilities}
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
        <li>
            <span class='headOfString'>{furnitureName}</span> {furniture}
        </li>
    </ul>
    <div class='clearBoth'></div>
    <div style='width:100%; font-size: 12px;'>
        <a href='property.php?propertyId={propertyId}' target='_blank'>подробнее</a>

        <div style='float: right;'>
            {favorites}
        </div>
    </div>
</div>