<div class="modal-dialog modal-md">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="exampleModalgridLabel"> Details</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="" method="GET" id="ShowForm" enctype="multipart/form-data" class="form theme-form">
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-body p-12">
                                <div class="table-responsive">
                                    <table class="table mb-0 table-borderless">
                                        <tbody>
                                            <tr>
                                                <th><span class="fw-medium">Key  :</span></th>
                                                <td>{{ $form->key}}</td>
                                            </tr>
                                            <tr>
                                                <th><span class="fw-medium">Title  :</span></th>
                                                <td>{{ $form->title}}</td>
                                            </tr>
                                            <tr>
                                                <th><span class="fw-medium">Description  :</span></th>
                                                <td>{{ $form->description}}</td>
                                            </tr>
                                            <tr>
                                                <th><span class="fw-medium">Class  :</span></th>
                                                <td>{{ $form->class}}</td>
                                            </tr>
                                            <tr>
                                                <th><span class="fw-medium">Alt  :</span></th>
                                                <td>{{ $form->alt}}</td>
                                            </tr>
                                            <tr>
                                                <th><span class="fw-medium">Link  :</span></th>
                                                <td>{{ $form->link}}</td>
                                            </tr>


                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <!--end card-body-->

                        <!--end card-body-->


                        <!--end card-body-->
                    </div>




                    <!--end card-->
                </div>





            </div>


        </form>

    </div>
</div>
