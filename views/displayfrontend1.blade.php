@if ($shabbatInfo['status_code'] != 200)
    <h3>Unable to display Shabbat Candle Lighting info</h3>
@else
    {!! $shabbatInfo['date'] !!}, {!! $shabbatInfo['parashat'] !!}
    <br />
    {!!$shabbatInfo['candle_lighting_title'] !!}
    <br />
    Havdalah Candle Lighting: {!! $shabbatInfo['havdalah_candle_lighting_time'] !!}
@endif