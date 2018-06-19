@include('styles.orgChart',[$public=>'../../public'])
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            {{isset($organisationChart) ? "Edit Organisation" : "Create Organisation"}}
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="">Organisation</li>
            <li class="active">{{isset($organisationChart) ? "Edit Organisation" : "Create Organisation"}}</li>
        </ol>
    </section>
    <!-- MODALS -->
    <section>
        @include('modals.alert')
        @include('modals.success')
        @include('modals.reassign')
        @include('modals.createDocument',["docType"=>"Organisation"])
    </section>

    <section class="content">

    <div class="tab-pane" id="createOrg">
        <div>To create an organisation you can use the tools below.</div>
        <br>
        <div id="chart-container" class="chart-container"></div>
        <div id="edit-panel" style="margin-bottom: 15px;margin-top:15px">
            <table style="float:left;display:inline-block">
                <tr style="height:35px">
                    <td><label class="selected-node-group" for="selected-node-category">Category:</label></td>
                    <td><input type="text" id="selected-node-category" class="selected-node-group" style="color:black"></td>
                    <td style="text-align: center;width:200px"><button style="vertical-align: middle" type="button" id="btn-update-node">Update</button></td>
                </tr>
                <tr style="height:35px">
                    <td><label class="selected-node-group" for="selected-node">Name:</label></td>
                    <td><select type="text" id="selected-node" class="selected-node-group" style="color:black"></select></td>
                    <td style="text-align: center"><button style="vertical-align: middle" type="button" id="btn-delete-node" style="margin-top: 7px">Delete</button></td>
                </tr>
                <tr style="height:35px">
                    <td><label class="selected-node-group" for="selected-node-title">Title:</label></td>
                    <td><input type="text" id="selected-node-title" class="selected-node-group nodeData_title" style="color:black" disabled></td>
                    <td style="text-align: center"><button type="button" style="vertical-align: middle" id="btn-add-nodes">Add staff</button></td>
                </tr>
                <input type="hidden" id="selected-node-id">
            </table>
        </div>
        <br>
        <div class="box-footer">
            <span class="pull-left">
            @if(isset($organisationChart))
                <button type="button" class="btn btn-warning msreassignmodal" data-toggle="modal" data-target="#msreassignModal" ><i class="fa fa-fw fa-user-plus"></i> Re-Assign</button>
            @else
                <button type="button" class="btn btn-warning mscreateDocument" data-toggle="modal" data-target="#mscreateDocumentModal" ><i class="fa fa-fw fa-user-plus"></i> Create</button>
            @endif

            </span>
        </div>
    </div>
    </section>
</div>
@push('scripts')
<script src="http://d3js.org/d3.v3.min.js" charset="utf-8"></script>

<script type="text/javascript">
    /*
     * 	CSC-ORGANIZATION-CHART
     * 	ADogan
     * */

    function wrap(text, width) {
        text.each(function() {
            var text = d3.select(this),
                words = text.text().split(/\s+/).reverse(),
                word,
                line = [],
                lineNumber = 0,
                lineHeight = 1.1, // ems
                y = text.attr("y"),
                dy = parseFloat(text.attr("dy"));
            dy = isNaN(dy) ? 0 : dy;
            tspan = text.text(null).append("tspan").attr("x", 0).attr("y", y).attr("dy", dy + "em");
            while (word = words.pop()) {
                line.push(word);
                tspan.text(line.join(" "));
                if (tspan.node().getComputedTextLength() > width) {
                    line.pop();
                    tspan.text(line.join(" "));
                    line = [word];
                    tspan = text.append("tspan").attr("x", 0).attr("y", y).attr("dy", ++lineNumber * lineHeight + dy + "em").text(word);
                }
            }
        });
    }


    function D3OrganizationChart(id, data, config) {
        var tree;

        var detailedView = false,
            scrollScale = 0.4, detaildModeScale = 0.7, isDrawed = false,
            svgLeftCoor = 0, svgTopCoor = 0, svgRightCoor = 0, svgBottomCoor = 0,
            nodeWidth = 300, nodeHeight = 157, indexCounter = 0, duration = 200;


        var render = function($container) {
            if (!data) {
                return;
            }
            data.x0 = 0;
            data.y0 = 0;

            var selectionCounter = 0;
            w = parseInt(config.width) || 600;
            h = parseInt(config.height) || 350;

            tree = d3.layout.tree().nodeSize([nodeWidth, nodeHeight]);

            var $$svgContainer = $container[0];
            $$svgContainer.style.width = w + "px";

            svg = d3.select($$svgContainer).append("svg")
                .attr("id","editSVG")
                .attr("xmlns", "http://www.w3.org/2000/svg")
                .attr("width", w)
                .attr("height", h)
                .attr("class", "container")
                .call(zm = d3.behavior.zoom().scaleExtent([0.1, 30]).on("zoom", function(d) {
                    redraw(d);
                })).on("dblclick.zoom", null)
                .append("g")
                .attr("id", "gggg");

            update(data);

        };

        // The tree fits in the container.
        /*var reScaleCurrentTree = function() {
         isDrawed = false;
         update(data);
         };*/

        var redraw = function(d) {
            scrollScale = d3.event.scale;
            svg.attr("transform", "translate(" + d3.event.translate + ")" + " scale(" + scrollScale + ")");

            if (config.detailOnZoom && !detailedView && scrollScale > detaildModeScale) {
                detailedView = true;
            } else if (detailedView && scrollScale < detaildModeScale) {
                detailedView = false;
            }
            update(undefined);
        };

        var edit = function(source) {
            if (!source) {
                source = data;
            }
            if (!tree) {
                return;
            }
            var node = svg.selectAll(".node");

            var editNodes = node.selectAll("text");

            editNodes.text(function(d){
                if (this.classList.contains("designation")) return d.designation;
                if (this.classList.contains("title")) return d.title;
                if (this.classList.contains("name")) return d.name;
                if (this.classList.contains("email")) return d.email;
                if (this.classList.contains("telephone")) return d.telephone;
            });

            var imageNodes = node.selectAll("text");

            node.selectAll("image").attr("xlink:href", function(d)
            {
                return "{{$router->pathFor('stored.path',['path'=>'images'])}}/" + d.image;
            });


        };

        var update = function(source) {
            if (!source) {
                source = data;
            }
            if (!tree) {
                return;
            }
            var nodes = tree.nodes(data),
                links = tree.links(nodes);

            // The y-axis ratio between nodes.
            nodes.forEach(function(d) {
                d.y = d.depth * 180;
            });

            svgLeftCoor = 0, svgRightCoor = 0, svgBottomCoor = 0;
            nodes.map(function(d) {
                svgLeftCoor = d.x < svgLeftCoor ? d.x : svgLeftCoor;
                svgRightCoor = d.x > svgRightCoor ? d.x : svgRightCoor;
                svgBottomCoor = d.y > svgBottomCoor ? d.y : svgBottomCoor;
            });

            if (!isDrawed) {
                scrollScale = (((w/3)-70) / (svgRightCoor - svgLeftCoor + nodeWidth));
                if ((svgBottomCoor + nodeHeight) * scrollScale > h) {
                    scrollScale = (h / ((svgBottomCoor + nodeHeight) * scrollScale));
                }
                svg.attr("transform", "translate(" + ((-1 * svgLeftCoor * scrollScale) + (w-70 - (svgRightCoor - svgLeftCoor + nodeWidth) * scrollScale) / 2) + " 10)" + "scale(" + scrollScale + ")");
                zm.scale(scrollScale);
                zm.translate([((-1 * svgLeftCoor * scrollScale) + (w-70 - (svgRightCoor - svgLeftCoor + nodeWidth) * scrollScale) / 2), 10]);
                isDrawed = true;

                if (config.detailOnZoom && scrollScale > detaildModeScale) {
                    detailedView = true;
                }
                if (scrollScale < 0.4) {
                    zm.scaleExtent([scrollScale, 30]);
                }
            }

            var node = svg.selectAll(".node")
                .data(nodes, function(d) {
                    return d.id || (d.id = Math.random().toString(36).substr(2, 10));
                });

            // Bring the parent node to the current position when the new node arrives.
            var nodeEnter = node.enter().append("g")
                .attr("id",function(d){return "ocNode_" + d.id})
                .attr("class", "node")
                .attr("transform", function() {
                    return "translate(" + source.x0 + "," + source.y0 + ")";
                })
                .style("fill-opacity", 0)
                .style("stroke-opacity", 0)
                .on("click", function(data) {
                    clickNode(data, this)
                });

            nodeEnter.append("rect")
                .attr("width", nodeWidth - 10)
                .attr("height", nodeHeight)
                .attr("rx", 10)
                .attr("ry", 10)
                .attr("class", "boundary");

            nodeEnter.append("text")
                .attr("text-anchor", "middle")
                .text(function(d){return d.designation})
                .style("fill-opacity", function() {
                    return detailedView ? 0 : 1;
                })
                .attr("class", "node-big designation")
                .attr("transform", function() {
                    return "translate(" + nodeWidth / 2 + "," + (5+((nodeHeight) / 6)) + ")";
                });

            nodeEnter.append("text")
                .attr("text-anchor", "middle")
                .text(function(d){return d.name})
                .style("fill-opacity", function() {
                    return detailedView ? 0 : 1;
                })
                .attr("class", "node-big name")
                .attr("transform", function() {
                    return "translate(" + nodeWidth / 2 + "," + (nodeHeight + 10) / 2 + ")";
                });

            nodeEnter.append("text")
                .attr("text-anchor", "middle")
                .text(function(d){return d.title})
                .style("fill-opacity", function() {
                    return detailedView ? 0 : 1;
                })
                .attr("class", "node-big title")
                .attr("transform", function() {
                    return "translate(" + nodeWidth / 2 + "," + (5+((nodeHeight *5) / 6)) + ")";
                });

            nodeEnter.selectAll("text").call(wrap,nodeWidth-50);

            var detailedViewContainer = nodeEnter.append("g")
                .attr("class", "node-detail")
                .attr("opacity", 0);

            drawDetailView(detailedViewContainer);

            // Düğümü kendi pozisyonuna getir veya var olanı güncelle.
            var nodeUpdate = node.transition()
                .duration(duration)
                .attr("transform", function(d) {
                    return "translate(" + d.x + "," + d.y + ")";
                })
                .style("fill-opacity", 1)
                .style("stroke-opacity", 1);

            nodeUpdate.selectAll(".node-big")
                .style("fill-opacity", function() {
                    return detailedView ? 0 : 1;
                });

            nodeUpdate.selectAll(".node-small")
                .style("fill-opacity", function() {
                    return detailedView ? 1 : 0;
                });

            nodeUpdate.select("g")
                .attr("opacity", function() {
                    return detailedView ? 1 : 0;
                });

            // Düğümü kaldırırken parent pozisyonuna doğru götür.
            var nodeExit = node.exit().transition()
                .duration(duration)
                .attr("transform", function() {
                    return "translate(" + source.x + "," + source.y + ")";
                })
                .style("fill-opacity", 0)
                .style("stroke-opacity", 0)
                .remove();

            nodeExit.select("rect")
                .attr("width", nodeWidth - 10)
                .attr("height", nodeHeight);

            nodeExit.select("text")
                .style("fill-opacity", 0);

            if (detailedView) {
                nodeExit.select("g")
                    .attr("opacity", 0);
            }

            var link = svg.selectAll(".link")
                .data(links, function(d) {
                    return d.target.id;
                });

            link.enter().insert("path", "g")
                .attr("class", "link")
                .attr("d", customDiagonal({
                    source: {
                        x: source.x0,
                        y: source.y0
                    },
                    target: {
                        x: source.x0,
                        y: source.y0
                    }
                }));

            link.transition()
                .duration(duration)
                .attr("d", customDiagonal());

            link.exit().transition()
                .duration(duration)
                .attr("d", customDiagonal({
                    source: {
                        x: source.x,
                        y: source.y
                    },
                    target: {
                        x: source.x,
                        y: source.y
                    }
                }))
                .remove();

            // Taşıma işlemi tamamlandıktan sonra eski pozisyonları temizle.
            nodes.forEach(function(d) {
                d.x0 = d.x;
                d.y0 = d.y;
            });
        };

        var drawDetailView = function(container) {
            if (config.showImageOnDetail) {
                container
                    .append("svg:image")
                    .attr("xlink:href", function(d) {
                        return "{{$router->pathFor('stored.path',['path'=>'images'])}}/" + d.image ;
                    })
                    .attr("width", nodeHeight - 44)
                    .attr("height", nodeHeight - 44)
                    .attr("x", 22+((nodeWidth - nodeHeight)/2))
                    .attr("y", 10);
                container
                    .append("text")
                    .attr("text-anchor", "middle")
                    .text(function(d){return "tel: " + d.telephone})
                    .attr("class", "node-small telephone")
                    .attr("transform", function() {
                        return "translate(" + nodeWidth / 2 + "," + (nodeHeight - 25) + ")";
                    });
                container
                    .append("text")
                    .attr("text-anchor", "middle")
                    .text(function(d){return "email: " + d.telephone})
                    .attr("class", "node-small email")
                    .attr("transform", function() {
                        return "translate(" + nodeWidth / 2 + "," + (nodeHeight - 10) + ")";
                    });
            }
        };

        var customDiagonal = function(dp) {
            var projection = function(d) {
                return [d.x, d.y];
            };

            var path = function(pathData) {
                return "M" + pathData[0] + ' ' + pathData[1] + ' ' + pathData[2] + ' ' + pathData[3];
            };

            function diagonal(diagonalPath, i) {
                if (dp) {
                    diagonalPath = dp;
                }
                var source = diagonalPath.source,
                    target = diagonalPath.target,
                    midpointX = ((source.x + nodeWidth / 2) + (target.x + nodeWidth / 2)) / 2,
                    midpointY = ((source.y + nodeHeight) + target.y) / 2,
                    pathData = [{
                        x: source.x + nodeWidth / 2,
                        y: source.y + nodeHeight
                    }, {
                        x: source.x + nodeWidth / 2,
                        y: midpointY
                    }, {
                        x: target.x + nodeWidth / 2,
                        y: midpointY
                    }, {
                        x: target.x + nodeWidth / 2,
                        y: target.y
                    }];
                pathData = pathData.map(projection);
                return path(pathData);
            }

            diagonal.path = function(x) {
                if (!arguments.length) return path;
                path = x;
                return diagonal;
            };
            return diagonal;
        };

        var clickNode = function(d, element) {

            d3.selectAll(".boundary").style("stroke",null);
            d3.selectAll(".boundary").style("fill",null);
            d3.select(element).select(".boundary").style("stroke","#3c8dbc");
            d3.select(element).select(".boundary").style("fill","#255b79");


            $('#selected-node-category').val(d.designation);

            $("#selected-node").empty().append('<option value="id">' + d.name + '</option>').val('id').trigger('change');


            $('#selected-node-title').val(d.title);
            $('#selected-node-id').val(d.id);

        };

        render(id);

        return {
            //reScaleCurrentTree: reScaleCurrentTree,
            clickNode : clickNode ,
            update:update,
            edit:edit
        };
    }
</script>
<script>

    var deleteParent;

@if(isset($organisationChart))
    var data={!! $organisationChart->data !!};
@else
    var data={"name":"name",
            "title":"title",
            "designation":"designation",
            "children":[],
            "image":"blank.png"};
@endif

    $(document).ready(function(){

        @include("scripts.select2Ajax",["selector"=>"#selected-node","placeholder"=>"Name","dropdownParent"=>"#createOrg",
        "initalSet"=>false,"ownerSelector"=>"nodeData"])

        var $container = $("#chart-container");

        var config = {
            width:$container.width(),
            height:750,
            detailOnZoom:true,
            onnoderightclicked:true,
            labelField:"name",
            imageField:"image",
            showImageOnDetail:true
        };

        var oc = new D3OrganizationChart($container, data, config);

        function searchTree(element, matchingId){
            if(element.id == matchingId){
                return element;
            }else if (element.children != null){
                var i;
                var result = null;
                for(i=0; result == null && i < element.children.length; i++){
                    result = searchTree(element.children[i], matchingId);
                }
                return result;
            }
            return null;
        }

        deleteParent = function (element){
            if(element.hasOwnProperty('parent')) delete element.parent;
            if (element.children != null){
                for(var i=0; i < element.children.length; i++){
                    deleteParent(element.children[i], data);
                }
            }
        }

        $("#btn-update-node").on("click",function(){
            if($("#selected-node-id").val() == "")
            {
                alertModal({
                    title:"Cannot Update - Node is not selected",
                    description:"Before update the node must be selected",
                    detail:""
                });
                return;
            }
            var node = searchTree(data, $("#selected-node-id").val());
            var d = $("#selected-node").select2('data')[0];
            node.name = d.text;
            node.designation = $("#selected-node-category").val();
            node.title = $("#selected-node-title").val();
            node.image = d.image ? d.image : "blank.png";
            node.telephone = d.telephone ? d.telephone : "";
            node.email = d.email ? d.email : "";
            node._id = d._id;
            oc.edit(data);
        });

        $('#btn-add-nodes').on('click', function() {
            var node = searchTree(data, $("#selected-node-id").val());
            if(node == null)
            {
                alertModal({
                    title:"Cannot Add Children",
                    description:"You must select a node, by clicking on it, to add children",
                    detail:""
                });
                return;
            }
            if (!Array.isArray(node.children)) node.children = [];
            var childIndex = node.children.push({
                "name":"name",
                "title":"title",
                "designation":"designation",
                "children":[],
                "image":"blank.png"
            });
            oc.update(data);
            var child = node.children[childIndex - 1];

            var element = d3.select("#ocNode_" + child.id);

            oc.clickNode(child,element[0][0])
        });

        $('#btn-delete-node').on('click', function() {
            var $node = searchTree(data, $("#selected-node-id").val());
            if ($node.hasOwnProperty("children") && $node.children.length > 0)
            {
                alertModal({
                    title:"Cannot Delete - Node has Children",
                    description:"This node cannot be deleted until all the staff have also been deleted",
                    detail:""
                });
                return;
            }

            $node.parent.children = $node.parent.children.filter(function(node){return node.id != $node.id});
            oc.update(data);
        });

    });

</script>
@endpush