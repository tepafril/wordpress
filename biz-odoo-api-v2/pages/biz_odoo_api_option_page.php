
<div class="wrap">
    <h1 class="wp-heading-inline">Sync Products, Categories, Brands</h1>
    <div id="odoo_sync_container">
        <div id="log-textarea" style="float:left;">
            <div id="text"></div>
            <div id="loading"></div>
        </div>
        <div id="loading_bar_container" style="float:left; width:50%;">
            <div class="loading_bar label-center"></div>
        </div>

        <?php
            $nonce = wp_create_nonce("biz_odoo_api_sync_nonce");
            $link = admin_url('admin-ajax.php?action=odoo_sync_action&nonce='.$nonce);
            echo '<a class="page-title-action" id="sync_odoo" data-nonce="' . $nonce . '" href="' . $link . '">Sync from Odoo</a>';
        ?>
    </div>
</div>


<style>
    .loading_bar{
        width: 50%;
        height: 220px;
        margin: 0px auto;
    }

    #loading_bar_container{
        display:hidden;
    }

    #odoo_sync_container{
        text-align:center;
    }
    
    #log-textarea{
        height: 240px;
        width: 50%;
        background-color:#fff;
        border: 1px solid #c1c1c1;
        overflow:scroll;
        text-align:left;
        font-size:10px;
        padding:5px;
    }
</style>