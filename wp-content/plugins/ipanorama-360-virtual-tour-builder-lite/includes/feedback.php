<?php
defined('ABSPATH') || exit;

$reasons = [
    "no_longer_needed" => [
        "label" => esc_html__("I do not need this plugin anymore", 'ipanorama')
    ],
    "found_better" => [
        "label" => esc_html__("I found another plugin that do the job better", 'ipanorama'),
        "input" => esc_html__("Please tell us which one", 'ipanorama')
    ],
    "how_to_use" => [
        "label" => esc_html__("I don't know how to use it", 'ipanorama')
    ],
    "temporary" => [
        "label" => esc_html__("This's temporary deactivation", 'ipanorama')
    ],
    "not_working" => [
        "label" => esc_html__("It's not working on my website", 'ipanorama')
    ],
    "other" => [
        "label" => esc_html__("Other", 'ipanorama'),
        "input" => esc_html__("Please share a reason...", 'ipanorama')
    ]
];
?>
<div id="ipanorama-feedback" class="ipanorama-feedback-wrap" style="display:none;">
    <div class="ipanorama-feedback">
        <div class="ipanorama-header">
            <h2 class="ipanorama-title"><?php esc_html_e("Quick Feedback", 'ipanorama'); ?></h2>
            <div class="ipanorama-close"></div>
        </div>
        <div class="ipanorama-data">
            <p class="ipanorama-description"><?php esc_html_e("Before you deactivate iPanorama 360 could you let us know why? Your feedback will help us improve the product, please tell us why did you decide to deactivate iPanorama 360. Thank you!", 'ipanorama'); ?></p>
            <div class="ipanorama-fields">
            <?php foreach($reasons as $key => $value) { ?>
                <div class="ipanorama-field">
                    <label><input type="radio" name="ipanorama-reason" <?php if ( $key === "temporary" ) echo 'checked'; ?> value="<?php echo esc_attr($key); ?>"><?php echo esc_attr($value["label"]); ?></label>
                    <?php if(isset($value["input"])) { ?>
                        <input type="text" name="reason-<?php echo esc_attr($key); ?>" placeholder="<?php echo esc_attr($value["input"]); ?>">
                    <?php } ?>
                    <?php if(isset($value["text"])) { ?>
                        <p><?php echo esc_html($value["text"]); ?></p>
                    <?php } ?>
                </div>
            <?php } ?>
            </div>
        </div>
        <div class="ipanorama-footer">
            <div class="ipanorama-btn ipanorama-submit"><?php esc_html_e("Submit & Deactivate", 'ipanorama'); ?></div>
            <div class="ipanorama-btn ipanorama-skip"><?php esc_html_e("Skip & Deactivate", 'ipanorama'); ?></div>
        </div>
    </div>
</div>

