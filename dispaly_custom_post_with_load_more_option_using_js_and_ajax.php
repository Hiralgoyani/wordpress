<!-- Create template and display custom post as blog default -->

<?php
/**
 * Template Name: Podcast
 */
get_header();
?>
    <div class="entry-content">
        <div class="wrap">
            <h1 class="title text-center blog-boxs">THE LATEST PODCASTS</h1>
     <?php

        $count_posts = wp_count_posts( 'blog' )->publish;
        $post_cnt = (int)ceil($count_posts/6) - 1;
        //echo "Count : ".$post_cnt;
        echo '<input type="hidden" value="'.$post_cnt.'" id="post_cnt" >';

        $args = array(
            'post_type' => 'blog',
            'post_status' => 'publish',
            'posts_per_page' => '6',
            'paged' => 1,
        );
        $blog_posts = new WP_Query( $args );
        ?>
 
        <?php if ( $blog_posts->have_posts() ) : ?>
            <div class="pcblog-posts">
                <?php 
                    $cnt = 0;
                    while ( $blog_posts->have_posts() ) : $blog_posts->the_post(); ?>
                        <?php 
                        if($cnt == 3){
                            ?>
                            <div class="podcast-sec-div">
                                <div class="podcast-content">
                                    <h1>LISTEN TO CHADWICK’S<br> PODCAST ON</h1>
                                </div>
                                <div class="podcast-img-grp">
                                    <a href="https://podcasts.apple.com/us/genre/podcasts/id26?mt=2" target="_blank"><img src="https://chadwicksapenter.com/https://chadwicksapenter.com//wp-content/uploads/2020/01/Bitmap-3.png" alt="apple"></a>
                                    <a href="https://podcasters.spotify.com" target="_blank"><img src="https://chadwicksapenter.com/https://chadwicksapenter.com//wp-content/uploads/2020/01/img_2-1.png" alt="spotify"></a>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                    <div class="small-12 large-4 columns">
                        <div class="pcblog-boxs">
                            <?php  
                                if ( has_post_thumbnail() ) {
                            ?>
                                <div class="img"><a href="<?php the_permalink(); ?>"><?php the_post_thumbnail(); ?></a></div>    
                            <?php
                                }else{
                            ?>
                                <div class="img"><a href="<?php the_permalink(); ?>"><img src="https://chadwicksapenter.com/https://chadwicksapenter.com//wp-content/uploads/2020/01/sampleimage.png" alt="blog image"></a></div>
                            <?php
                                } 
                            ?>                            
                            <span class="date"><?php echo date('F j, Y', strtotime($blog_posts->posts[$cnt]->post_date)); ?></span>
                            <h5><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h5>
                        </div>
                    </div>
                <?php 
                    $cnt = $cnt + 1;
                    endwhile; ?>
            </div>
            <div class="pcblog-buttons">
                <button class="pcloadmore btn btn-danger">LOAD MORE</button>                
            </div>
        <?php endif; ?>
        </div>
    </div>
<?php
get_footer();
?>





<!-- Js for load more with ajax  -- >

<script type="text/javascript">
    var page = 2;
    jQuery(function($) {

        var smpl_cnt = 1;
        var post_cnt = jQuery('#post_cnt').val();
        //console.log('test '+ post_cnt);

        
        $('body').on('click', '.pcloadmore', function() {
            var data = {
                'action': 'load_pcposts_by_ajax',
                'page': page,
                'security': blog.security
            };
     
            $.post(blog.ajaxurl, data, function(response) {
               if(response.match(/[a-z]/i)){
                    $('.pcblog-posts').append(response);
                    page++;   
                    if(post_cnt <= smpl_cnt){
                        $('.pcloadmore').hide();    
                    }else{
                        smpl_cnt = smpl_cnt + 1;            
                    }
                }else{                 
                    $('.pcloadmore').hide();    
                }            
            });
        });
    });
</script>





<!-- function file, whicha have localize script file and load more action function -->

<?php
// localize script file
wp_register_script( 'custom-script', get_stylesheet_directory_uri(). '/js/load-more.js', array('jquery'), false, true );
$script_data_array = array('ajaxurl' => admin_url( 'admin-ajax.php' ), 'security' => wp_create_nonce( 'load_more_posts' ));
wp_localize_script( 'custom-script', 'blog', $script_data_array );
wp_enqueue_script( 'custom-script' );


//  code for blog load more ajax
add_action('wp_ajax_load_pcposts_by_ajax', 'load_pcposts_by_ajax_callback');
add_action('wp_ajax_nopriv_load_pcposts_by_ajax', 'load_pcposts_by_ajax_callback');

function load_pcposts_by_ajax_callback() {
    check_ajax_referer('load_more_posts', 'security');
    $paged = $_POST['page'];
    $args = array(
        'post_type' => 'blog',
        'post_status' => 'publish',
        'posts_per_page' => '6',
        'paged' => $paged,
    );
    $blog_posts = new WP_Query( $args );
    ?>
 
    <?php if ( $blog_posts->have_posts() ) : ?>
        <?php $cnt = 0;
                while ( $blog_posts->have_posts() ) : $blog_posts->the_post(); ?>
                <div class="small-12 large-4 columns">
                    <div class="pcblog-boxs">
                        <?php  
                                if ( has_post_thumbnail() ) {
                            ?>
                                <div class="img"><?php the_post_thumbnail(); ?></div>    
                            <?php
                                }else{
                            ?>
                                <div class="img"><img src="https://chadwicksapenter.com/https://chadwicksapenter.com//wp-content/uploads/2020/01/sampleimage.png" alt="blog image"></div>
                            <?php
                                } 
                            ?>
                        <span class="date"><?php echo date('F j, Y', strtotime($blog_posts->posts[$cnt]->post_date)); ?></span>
                        <h5><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h5>
                    </div>
                </div>
            <?php 
                $cnt = $cnt + 1;
                endwhile; ?>
        <?php
    endif;
 
    die();
}