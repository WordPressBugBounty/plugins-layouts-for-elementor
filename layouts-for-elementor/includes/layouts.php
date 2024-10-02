<div class="lfe-body">
    <div class="lfe-header">
        <h1 class="wp-heading-inline"><?php esc_html_e('Layouts for Elementor', 'layouts-for-elementor'); ?></h1>
    </div>
    <div id="lfe-wrap" class="lfe-wrap">
        <div class="lfe-header">
            <div class="lfe-title lfe-is-inline"><h2 class="lfe-title"><?php esc_html_e('Elementor Template Kits:', 'layouts-for-elementor'); ?></h2></div>
            <div class="lfe-sync lfe-is-inline">
                <a href="javascript:void(0);" class="lfe-sync-btn"><?php esc_html_e('Sync Now', 'layouts-for-elementor'); ?></a>
            </div>
        </div>
        <?php
        $categorys = LFE\API\Layouts_Remote::lfe_get_instance()->categories_list();
        if (!empty($categorys['category']) && $categorys != "") {
            ?>
            <div class="collection-bar">
                <h4><?php esc_html_e('Browse by Industry', 'layouts-for-elementor'); ?></h4>
                <ul class="collection-list">
                    <li><a class="lfe-category-filter active" data-filter="all" href="javascript:void(0)"><?php esc_html_e('All', 'layouts-for-elementor'); ?></a></li>
                    <?php
                    foreach ($categorys['category'] as $cat) {
                        ?>
                        <li><a href="javascript:void(0)" class="lfe-category-filter" data-filter="<?php echo esc_attr($cat['slug']); ?>" ><?php echo esc_html($cat['title']); ?></a></li>
                        <?php
                    }
                    ?>
                </ul>
            </div>
            <?php
        }
        ?>

        <div class="lfe_wrapper">
            <?php
            $data = LFE\API\Layouts_Remote::lfe_get_instance()->templates_list();
            $i = 0;
            if (!empty($data['templates']) && $data !== "") {
                foreach ($data['templates'] as $key => $val) {
                    $categories = "";
                    foreach ($val['category'] as $ckey => $cval) {
                        $categories .= $cval . " ";
                    }
                    ?>
                    <div class="lfe_box lfe_filter <?php echo esc_attr(sanitize_title($categories)); ?>">
                        <div class="lfe_box_widget">
                            <div class="lfe-media">
                                <img src="<?php echo esc_url($val['thumbnail']); ?>" alt="screen 1">
                                <?php if ($val['is_pro'] == true) { ?>
                                    <span class="pro-btn"><?php echo esc_html__('PRO', 'layouts-for-elementor'); ?></span>
                                <?php } else { ?>
                                    <span class="free-btn"><?php echo esc_html__('FREE', 'layouts-for-elementor'); ?></span>
                                <?php } ?>
                            </div>
                            <div class="lfe-template-title"><?php echo esc_html($val['title'], 'layouts-for-elementor'); ?></div>
                            <div class="lfe-btn">
                                <a href="javascript:void(0)" data-url="<?php echo esc_url($val['url']); ?>" title="<?php echo esc_html__('Preview', 'layouts-for-elementor'); ?>" class="btn pre-btn previewbtn"><?php echo esc_html__('Preview', 'layouts-for-elementor'); ?></a>
                                <?php if ($val['is_pro'] == true) { ?>
                                    <a href="https://layoutsforelementor.com/pro/" target="_blank" title="<?php echo esc_html__('Buy Pro', 'layouts-for-elementor'); ?>" class="btn ins-btn"><?php echo esc_html__('Buy Pro', 'layouts-for-elementor'); ?></a>
                                <?php } else {
                                    ?>
                                    <a href="javascript:void(0)" title="<?php echo esc_html__('Install', 'layouts-for-elementor'); ?>" class="btn ins-btn installbtn"><?php echo esc_html__('Install', 'layouts-for-elementor'); ?></a>
                                <?php }
                                ?>
                            </div>
                        </div>
                    </div>

                    <!-- Preview popup div start -->
                    <div class="lfe-preview-popup" id="preview-in-<?php echo esc_attr($i); ?>">
                        <div class="lfe-preview-container">
                            <div class="lfe-preview-header">
                                <div class="lfe-preview-title"><?php echo esc_html($val['title']); ?></div>
                                <?php if ($val['is_pro'] == false) { ?>
                                    <div class="lfe-import">
                                        <p class="lfe-msg"><?php esc_html_e('Import this template via one click', 'layouts-for-elementor'); ?></p>
                                        <span class="lfe-loader"></span>

                                        <a href="javascript:void(0)" class="btn ins-btn lfe-import-btn" disabled data-template-id="<?php echo esc_attr($val['id']); ?>" ><?php esc_html_e('Import Template', 'layouts-for-elementor'); ?></a>
                                        <a href="#" class="btn ins-btn lfe-edit-template" style="display:none" target="_blank"><?php esc_html_e('Edit Template', 'layouts-for-elementor'); ?></a>
                                    </div>

                                    <span><?php esc_html_e('OR', 'layouts-for-elementor'); ?></span>

                                    <div class="lfe-import lfe-page-create">
                                        <p><?php esc_html_e('Create a new page from this template', 'layouts-for-elementor'); ?></p>
                                        <input type="text" class="lfe-page-name-<?php echo esc_attr($val['id']); ?>" placeholder="Enter a Page Name" />
                                        <a href="#" class="btn ins-btn lfe-create-page-btn" data-template-id="<?php echo esc_attr($val['id']); ?>" ><?php esc_html_e('Create New Page', 'layouts-for-elementor'); ?></a>
                                    </div>

                                    <span class="lfe-loader-page"></span>

                                    <div class="lfe-import lfe-page-edit" style="display:none" >
                                        <p><?php esc_html_e('Your template is successfully imported!', 'layouts-for-elementor'); ?></p>
                                        <a href="#" class="btn ins-btn lfe-edit-page" target="_blank" ><?php esc_html_e('Edit Page', 'layouts-for-elementor'); ?></a>
                                    </div>
                                <?php } ?>
                                <span class="lfe-close-icon"></span>

                                <a href="<?php echo esc_url($val['url']); ?>" class="lfe-dashicons-link" title="<?php esc_html_e('Open Preview in New Tab', 'layouts-for-elementor'); ?>" rel="noopener noreferrer" target="_blank">
                                    <span class="lfe-dashicons"></span>
                                </a>
                            </div>
                            <iframe width="100%" height="100%" src=""></iframe>
                        </div>
                    </div>
                    <!-- Preview popup div end -->

                    <!-- Install popup div start -->
                    <div class="lfe-install-popup" id="content-in-<?php echo esc_attr($i); ?>">
                        <div class="lfe-container">
                            <div class="lfe-install-header">
                                <div class="lfe-install-title"><?php echo esc_html($val['title']); ?></div>
                                <span class="lfe-close-icon"></span>
                            </div>
                            <div class="lfe-install-content">
                                <p class="lfe-msg"><?php esc_html_e('Import this template via one click', 'layouts-for-elementor'); ?></p>
                                <div class="lfe-btn">
                                    <span class="lfe-loader"></span>
                                    <a href="javascript:void(0)" class="btn ins-btn lfe-import-btn" data-template-id="<?php echo esc_attr($val['id']); ?>" ><?php esc_html_e('Import Template', 'layouts-for-elementor'); ?></a>
                                    <a href="#" class="btn ins-btn lfe-edit-template" style="display:none" target="_blank"><?php esc_html_e('Edit Template', 'layouts-for-elementor'); ?></a>
                                </div>

                                <p class="lfe-horizontal"><?php esc_html_e('OR', 'layouts-for-elementor'); ?></p>

                                <div class="lfe-page-create">
                                    <p><?php esc_html_e('Create a new page from this template', 'layouts-for-elementor'); ?></p>
                                    <input type="text" class="lfe-page-<?php echo esc_attr($val['id']); ?>" placeholder="Enter a Page Name" />
                                    <div class="lfe-btn">
                                        <a href="javascript:void(0)" style="padding: 0;" class="btn pre-btn lfe-create-page-btn" data-name="crtbtn" data-template-id="<?php echo esc_attr($val['id']); ?>" ><?php esc_html_e('Create New Page', 'layouts-for-elementor'); ?></a>
                                        <span class="lfe-loader-page"></span>
                                    </div>
                                </div>
                                <div class="lfe-create-div lfe-page-edit" style="display:none" >
                                    <p style="color: #000;"><?php esc_html_e('Your page is successfully imported!', 'layouts-for-elementor'); ?></p>
                                    <div class="lfe-btn">
                                        <a href="#" class="btn pre-btn lfe-edit-page" target="_blank" ><?php esc_html_e('Edit Page', 'layouts-for-elementor'); ?></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Install popup div end -->
                    <?php
                    $i++;
                }
            } else {
                echo esc_html($data['message']);
            }
            ?>
        </div>
    </div>
</div>
