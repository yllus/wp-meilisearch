<section>
    <ol id="results" class="content">
        <!-- documents matching requests -->
    </ol>
</section>


<style>
      em {
        color: hsl(204, 86%, 25%);
        font-style: inherit;
        background-color: hsl(204, 86%, 88%);
      }

      #results {
        max-width: 900px;
        margin: 20px auto 0 auto;
        padding: 0;
      }

      .notification {
        display: flex;
        justify-content: center;
      }

      .level-left {
        margin-right: 50px;
      }

      .document {
        padding: 20px 20px;
        background-color: #f5f5f5;
        border-radius: 4px;
        margin-bottom: 20px;
        display: flex;
      }

      .document ol {
        flex: 0 0 75%;
        max-width: 75%;
        padding: 0;
        margin: 0;
      }

      .document .image {
        max-width: 25%;
        flex: 0 0 25%;
        padding-left: 30px;
        box-sizing: border-box;
      }

      .document .image img {
        width: 100%;
      }

      .field {
        list-style-type: none;
        display: flex;
        flex-wrap: wrap;
      }

      .field:not(:last-child) {
        margin-bottom: 7px;
      }

      .attribute {
        flex: 0 0 25%;
        max-width: 25%;
        text-align: right;
        padding-right: 10px;
        box-sizing: border-box;
        text-transform: uppercase;
        color: rgba(0,0,0,.7);
      }

      .content {
        max-width: 75%;
        flex: 0 0 75%;
        box-sizing: border-box;
        padding-left: 10px;
        color: rgba(0,0,0,.9);
        overflow-wrap: break-word;
      }
</style>