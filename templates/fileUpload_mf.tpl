<div class="lazy page-box">
    <div class="lazy page-title">
        {$subTitle}
    </div>

    <div class="lazy page-content">

        <div>
            <img class="lazy page-image" src="{$image}">
        </div>

        <div>
            {$description}
            <br/>
            Maximum dimensions: {$dimX} x {$dimY} pixels, {$maxsize}
        </div>

        <form class="page-grid page-column-2" action="" method="post" enctype="multipart/form-data" name="form1">
            <input class="lazy page-button-fill" type="file" name="userfile">
            <input class="lazy page-button-fill" type="submit" name="Submit" value="Upload">
        </form>
    </div>
</div>