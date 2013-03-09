<div class='realtyObject' propertyId='{propertyId}'>
    <div class="serviceMarks">
        <div class='numberOfRealtyObject'>{number}</div>
        {favorites}
    </div>
    <div class="mainContent">
        <div class="addressInShort">
            <span class="unimportantText">{typeOfObject}</span> <a href='property.php?propertyId={propertyId}' target='_blank'>{address}</a>
        </div>
        <div class="costOfRentingInShort">
            <span class="costOfRentingInShortString">{costOfRenting}</span> <span class="unimportantText">{currency}</span> <span class="unimportantText">{utilities}</span>
        </div>
        <div class="clearBoth"></div>
        <div>
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
        <div class='advertActions'>
            <a href='property.php?propertyId={propertyId}' target='_blank'>подробнее</a>
        </div>
    </div>
</div>