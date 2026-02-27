<!--faqs-->
<?php 
if( have_rows('faqs') ):
// loop through the rows of data
while ( have_rows('faqs') ) : the_row();

    // display a sub field value
echo'<div id="faq_container" class="faq-container"> 
    <div class="faq">
        <div class="faq__question"> <span class="faq__question-text">' . get_sub_field('faq_question') . '</span><span class="faq__toggle-icon fa-regular fa-circle-caret-down"></span></div>
            <div class="faq__answer-wrapper">
                <div class="faq__answer"><span>' . get_sub_field('faq_answer') . '</span></div>
            </div>
    </div>
    </div>';

endwhile; else : endif; ?> 