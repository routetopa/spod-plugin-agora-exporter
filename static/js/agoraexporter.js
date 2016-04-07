AGORAEXPORTER = {};


AGORAEXPORTER.init = function()
{
    var room = JSON.parse(AGORAEXPORTER.completeGraph);
    room.nodes.forEach(function(node)
    {
        var elem = $("#container");
        console.log(node);

        if(node.father == null)
        {
            elem.append("<ul id='root_"+node.originalId+"'></ul>");
        }
        else
        {
            elem = $("#node_" + node.father.originalId);
            elem.append("<ul id='root_"+node.originalId+"'></ul>");
        }

        elem = $("#root_" + node.originalId);
        elem.append("<li id='node_"+node.originalId+"'>"+ node.name + " : " + node.content + " -> " + new Date(node.createStamp*1000) + "</li>");

        //DATALET
        if(typeof node.datalet != "undefined")
        {
            elem = $("#node_" + node.originalId);
            elem.append("<div id='datalet_"+node.originalId+"'></div>");

            var params = JSON.parse(node.datalet.params);
            var fields = node.datalet.fields.split('","');

            for(var i=0; i<fields.length; i++)
                fields[i] = fields[i].replace('"', '');

            ODE.loadDatalet(node.datalet.component, params, fields, node.datalet.data, "datalet_" + node.originalId);
        }
    });
};

