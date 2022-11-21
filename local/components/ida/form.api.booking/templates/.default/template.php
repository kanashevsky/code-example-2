<?php
/**
 * @var $arParams array
 * @var $arResult array
 */
$buildingId = $arResult['BUILDING_ID'];
?>
<div class="form">
    <div class="booking" style='display:none;' id='form-feedback'>
        <? app()->IncludeComponent('cpeople:form.callback', '') ?>
    </div>
    <!-- Доступность лота, проверка -->
    <div class="booking" id='booking_load' style='display:none;'>

        <div class="loaderWrapper">
            <div class="loaderInner">
                <div class="loader">
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </div>

        <div class="bookingTitle loadTitle">Происходит бронирование <br> Пожалуйста, ожидайте</div>
    </div>

    <!-- Превышен интервал ожидания / Проверка недоступна. -->
    <div class="booking" id='booking_error' style='display:none;'>

        <div class="bookingIcon _alert">
            <svg>
                <use xlink:href="#sym-alert"></use>
            </svg>
        </div>

        <div class="bookingTitle">К сожалению, проверка доступности бронирования сейчас
            недоступна.
        </div>

        <div class="bookingText">Чтобы получить информацию о выбранном лоте позвоните по
            телефону <a href="tel:+74957836816">+7 (495) 783-68-16</a>
        </div>

        <div class="btnWrap">
            <button id='re-booking'>Попробовать еще раз</button>
            <button id='feedback-booking'>Оставить заявку</button>
        </div>
    </div>

    <!-- Лот уже недоступен -->
    <div class="booking" id='booking_nofree' style='display:none;'>

        <div class="bookingIcon _alert">
            <svg>
                <use xlink:href="#sym-alert"></use>
            </svg>
        </div>

        <div class="bookingTitle">Данный объект уже недоступен</div>

        <div class="btnWrap">
            <button class="_oneBtn">Посмотреть другие объекты</button>
        </div>
    </div>

    <!-- Бронирование -->
    <div class="booking" id='booking_free'>
        <div class="bookingTitle">Бронирование объекта</div>

        <div class="btnWrap">
            <button class='booking_success dataLayerButtons' data-label="Подтвердить бронирование">Подтвердить бронирование</button>
            <button class='js-close-popup-form dataLayerButtons' data-label="Отменить бронирование">Отменить</button>
        </div>
    </div>

    <!-- Успешно -->
    <div class="booking" id='booking_success' style='display:none;'>

        <div class="bookingIcon">
            <svg>
                <use xlink:href="#sym-complete"></use>
            </svg>
        </div>
        <?if ($buildingId == '7AB6774D-6C17-EB11-BDFA-00155DFC99C4' ||
             $buildingId == '8A993516-6C17-EB11-BDFA-00155DFC99C4') { ?>
            <div class="bookingTitle">Уважаемый клиент </div>
            <?
            $currentDay = date('d', time());
            $otherDate = date('.m.Y H:i', time());
            ?>
            <div class="bookingText">
                Квартира забронирована за вами и снята с продажи до <?=$currentDay + 1?><?=$otherDate?>
            </div>
            <div class="bookingLists">
                <p>Для завершения бронирования и начала оформления договора, вам необходимо:</p>
                <ol>
                    <li>Установить мобильное приложение MR Group по ссылкам ниже</li>
                    <li>В течение 24 часов оплатить в мобильном приложении счет на бессрочное бронирование (на все время оформления сделки)</li>
                </ol>
                <p>Или дождаться звонка менеджера и получить инструкции по дальнейшим шагам.</p>
            </div>
            <div class="bookingBtnLink">
                <?= getTextOnPages('mobile-apps') ?>
            </div>

            <a id="bookingPayLink" class="btn">Оплатить бронирование</a>

        <? } else { ?>
            <div class="bookingTitle">Поздравляем! Данный объект забронирован за Вами!</div>
            <div class="bookingText">
                Лот бронируется на 24 часа.<br>
                Менеджер свяжется с Вами в течении часа с момента оставления заявки.
            </div>
        <? } ?>
    </div>
</div>

</div>
