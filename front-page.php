<?php
/**
 * The front page template file
 *
 * If the user has selected a static page for their homepage, this is what will
 * appear.
 * Learn more: https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Twenty_Seventeen
 * @since 1.0
 * @version 1.0
 */

get_header(); ?>

    <div id="primary" class="content-area" id="app">
        <main id="main" class="site-main" role="main" >
            <article id="post-<?php the_ID(); ?>" <?php post_class( 'twentyseventeen-panel ' ); ?> >
                <div class="entry-content">
                    <form
                          @submit.prevent="$validator.validateAll(); ;  sendForm()" >
                        <div class="form-group">
                            <label class="control-label" for="first">Name<span
                                        aria-hidden="true">*</span></label>
                            <input name="name" type="text" class="form-control" id="name"
                                   v-model="name" v-validate="frontValidation.name">
                            <span class="text-danger" v-show="errors.has('name')">{{ errors.first( 'name') }}</span>
                        </div>
                        <div class="form-group">
                            <label class="control-label" for="phone">Phone<span
                                        aria-hidden="true"></span></label>
                            <input name="phone" type="text" class="form-control" id="phone"
                                   v-model="phone" v-validate="frontValidation.phone">
                            <span class="text-danger" v-show="errors.has('phone')">{{ errors.first( 'phone') }}</span>
                        </div>
                        <div class="form-group">
                            <label class="control-label" for="ade">Age<span
                                        aria-hidden="true">*</span></label>
                            <input name="age" type="text" class="form-control" id="age"
                                   v-model="age" v-validate="frontValidation.age">
                            <span class="text-danger" v-show="errors.has('age')">{{ errors.first( 'age') }}</span>
                        </div>
                        <div class="form-group col-sm-6">
                            <button type="submit" class="btn-submit-conf-reg btn btn-sm" >
                                <span>Submit</span>
                            </button>
                        </div>
                    </form>
                </div>
            </article>

        </main><!-- #main -->
    </div><!-- #primary -->
    <script>

    </script>
<?php get_footer();
