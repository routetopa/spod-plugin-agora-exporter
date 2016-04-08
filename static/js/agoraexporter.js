AGORAEXPORTER = {};


AGORAEXPORTER.getPrettyDate = function(timestamp)
{
    var date = new Date(timestamp*1000);
    var monthNames = ["January", "February", "March","April", "May", "June", "July","August", "September", "October","November", "December"];
    var day = date.getDate();
    var monthIndex = date.getMonth();
    var year = date.getFullYear();
    var hours = date.getHours();
    var minutes = date.getMinutes();
    return (day + ' ' + monthNames[monthIndex] + ' ' + year + ' ' + hours + ':' + minutes);
};

AGORAEXPORTER.init = function()
{
    var room = JSON.parse(AGORAEXPORTER.completeGraph);
    var elem = $("#container");
    var roomTopic = room.nodes.splice(0,1);

    elem.append("<h2>"+roomTopic[0].content+"</h2>");
    elem.append("<ol class='rounded-list' id='root_"+roomTopic[0].originalId+"'></ol>");
    elem = $("#root_" + roomTopic[0].originalId);

    room.nodes.forEach(function(node)
    {
        elem = $("#root_" + node.father.originalId);
        if(elem.length == 0)
        {
            elem = $("#node_" + node.father.originalId);
            elem.append("<ol id='root_" + node.father.originalId + "'></ol>");
            elem = $("#root_" + node.father.originalId);
        }
        else
        {
            elem = $(elem[0]);
        }

        elem.append("<li id='node_"+node.originalId+"'><a>"+ node.name + " : <strong>" + node.content + "</strong> -> " + AGORAEXPORTER.getPrettyDate(node.createStamp) + "</a></li>");

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

