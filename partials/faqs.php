<!--faqs-->
<?php 
if( have_rows('faqs') ):
// loop through the rows of data
while ( have_rows('faqs') ) : the_row();

    // display a sub field value
echo'<div id="faq_container"> 
    <div class="faq">
        <div class="faq_question"> <span class="question">' . get_sub_field('faq_question') . '</span><span class="accordion-button-icon fa-regular fa-circle-caret-down"></span></div>
            <div class="faq_answer_container">
                <div class="faq_answer"><span>' . get_sub_field('faq_answer') . '</span></div>
            </div>
    </div>
    </div>';

endwhile; else : endif; ?> 