// let insertBtn = document.getElementById('insertBtn')
    window.onload = ()=>{

        let myModal = document.getElementById('myModal')
        let myInput = document.getElementById('myInput')
        // let infoBtn = document.querySelector('.bi-info-circle-fill')
        myModal.addEventListener('shown.bs.modal', function () {
            if (myInput != undefined) {
                myInput.focus()
            }
        })
        function showMsg(msg){
            $('#myModal').modal('show')
            $('#alertMsg').text(msg)
        }
        $('.bi-info-circle-fill').click(()=>{
            $(".descriptionInfo").removeClass('d-none');
        })
        $('.bi-x-circle-fill').click(()=>{
            $(".descriptionInfo").addClass('d-none');
        })
        let pdinfoType = $('#pdInfo').attr('data-type');
        console.log('pdinfoType',pdinfoType)
        $('#insertPage').addClass('active')

        insertPage.onclick = ()=>{
            // history.pushState(state：物件(可以是空物件), title：通常是空字串, url：要改成的url);
            // 查詢頁面過來會帶查詢參數，用原本頁面網址替換掉
            history.pushState({},'',window.location.pathname)
            console.log(window.location.pathname,'5555555555')
            $('#maininfoTitle').text('產品主要資訊(新增)')
            $('#updateBtn').addClass('d-none')
            $('#insertBtn').removeClass('d-none')
            $('#pdInfo').attr('data-type', 'insert'); //setAttribute
            pdinfoType = $('#pdInfo').attr('data-type'); //getAttribute
            // pdInfo.setAttribute('data-type','insert')
            // pdinfoType = pdInfo.getAttribute('data-type')
            console.log('pdinfoType',pdinfoType)
            $('#insertPage').addClass('active')
            $('#updatePage').removeClass('active')
            clearInput()

        }
        updatePage.onclick = ()=>{
            $('#pdSeries').val('')
            edit()
        }
        // 查詢產品，點選過來時
        const urlParams = new URLSearchParams(window.location.search);
        const mode = urlParams.get('mode') 
        console.log('urlParams',urlParams,'mode',mode)
        const seriesID = urlParams.get('id') 
        if (mode === 'edit' && seriesID) {
            console.log('mode裡',seriesID)
            edit()
            $('#pdSeries').val(seriesID)
            showProduct(seriesID)
            // console.log('mode裡pdSeries',pdSeries.innerText)

        }
     
        function edit(){
            $('#maininfoTitle').text('產品主要資訊(修改)')
            $('#insertBtn').addClass('d-none')
            $('#updateBtn').removeClass('d-none')
            $('#pdInfo').attr('data-type','update')
            pdinfoType = $('#pdInfo').attr('data-type')
            $('#insertPage').removeClass('active')
            $('#updatePage').addClass('active')
            console.log('pdinfoType',pdinfoType)
            $('#pdSeries').attr("placeholder", "請搜尋產品系列編號");
        }

        function clearInput(){
            $('#categoryOptions').val('default')
            $('#pdName').val('')
            $('#showCoverImg').html('');
            $('#showOthersImgs').html('');
            $('#showDescImgs').html('');
            $('input[name^="description"]').each((_,elem)=>{
                $(elem).val('')
            })

        }
        // 查詢產品系列編號是否使用
        pdSeries.onchange = (event)=>{
            console.log('pdSeries裡pdinfoType',pdinfoType)
            if(pdinfoType === 'insert'){
                console.log('event.target.value',event.target.value)
                // fetch(`http://localhost/testpet/public/api/product_back/info/select/${event.target.value}`,{
                fetch(`http://localhost/happypet/happypet_back/public/api/product_back/info/check/${event.target.value}?pdinfoType=${pdinfoType}`,{
                    method:'get',
                })
                .then(response=>{
                    return response.json()
                })
                .then(({message})=>{
                    if (message != undefined) {
                        console.log('我是message.message')
                        showMsg(message.message)
                    }
                })
                // console.log('我是if')
            }else{
                // console.log('我是else')
                showProduct(event.target.value)
            }

        }
        function showProduct(seriesID){
            $.get(`http://localhost/happypet/happypet_back/public/api/product_back/info/show/${seriesID}`)
            .done(({seriesProduct,message})=>{
                clearInput()
                // 如果系列編號不存在即顯示訊息
                if(message){
                    // message ? showMsg(message): null ;
                    showMsg(message)
                    clearInput()
                    return
                }
                console.log('seriesProduct = ',seriesProduct)
                seriesProduct.forEach((seriesPD)=>{
                    let {category_id,series_name,pic_category_id,...products} = seriesPD

                    $('#pdName').val(series_name)
                    $('#categoryOptions').val(category_id)
                    $('input[name^="description"]').each((i,elem)=>{
                        // console.log(i,'----------',elem)
                        console.log(products,products["description1"])
                        descKey = `description${i+1}` 
                        $(elem).val(products[descKey])
                    })
                    // console.log('pic_category_id',pic_category_id)
                    switch (pic_category_id) {
                        case 1:
                            showCoverImg.innerHTML = `<img src="${seriesPD.img}">`
                            break;
                        case 2:
                            showOthersImgs.innerHTML += `<img src="${seriesPD.img}">`
                            break;
                        case 3:
                            showDescImgs.innerHTML += `<img src="${seriesPD.img}">`
                            break;
                        default:
                            break;
                    }
                })
            })
            // fetch(`http://localhost/happypet/happypet_back/public/api/product_back/info/show/${seriesID}`,{
            //     method:'post',
            // })
            // .then(response=>{
            //     return response.json()
            // })
            // .then(({seriesProduct,message})=>{ 
            //     clearInput()
            //     // 如果系列編號不存在即顯示訊息
            //     if(message){
            //         // message ? showMsg(message): null ;
            //         showMsg(message)
            //         clearInput()
            //         return
            //     }
            //     console.log('seriesProduct = ',seriesProduct)
            //     seriesProduct.forEach((seriesPD)=>{
            //         let {category_id,series_name,pic_category_id,...products} = seriesPD

            //         $('#pdName').val(series_name)
            //         $('#categoryOptions').val(category_id)
            //         $('input[name^="description"]').each((i,elem)=>{
            //             // console.log(i,'----------',elem)
            //             console.log(products,products["description1"])
            //             descKey = `description${i+1}` 
            //             $(elem).val(products[descKey])
            //         })
            //         // console.log('pic_category_id',pic_category_id)
            //         switch (pic_category_id) {
            //             case 1:
            //                 showCoverImg.innerHTML = `<img src="${seriesPD.img}">`
            //                 break;
            //             case 2:
            //                 showOthersImgs.innerHTML += `<img src="${seriesPD.img}">`
            //                 break;
            //             case 3:
            //                 showDescImgs.innerHTML += `<img src="${seriesPD.img}">`
            //                 break;
            //             default:
            //                 break;
            //         }
            //     })
            // })
        }
        // 修改
        // updateBtn.addEventListener('click', (event)=>{
        $("#updateBtn").click( (event)=>{
            event.preventDefault();
            let formData = new FormData(document.getElementById('pdInfo'));
            
            fetch(`http://localhost/happypet/happypet_back/public/api/product_back/info/update`,{
                method:'post',
                body:formData,
            })
            .then(response=>{
                return response.json()
            })
            .then((data)=>{
                if (data.message) {
                    // console.log(data.message);
                    showMsg(data.message)
                    setTimeout(()=>{
                        location.reload()// 刷新頁面
                        window.location.href = `http://localhost/happypet/happypet_front/40_product/back/main_info.html`;
                    },2000)
                } else if (data.error) {
                    showMsg(data.error)
                }
             })
        // })
        })

        // 預覽圖片
        $('#coverimg').change(()=>{
            let imgreader = new FileReader();
            console.log('imgreader----->',imgreader)
            console.log('imgreader----->',imgreader)
            imgreader.readAsDataURL(coverimg.files[0])
            $('#showCoverImg').html('')
            imgreader.onload = (event)=>{
                // showCoverImg.innerHTML += `<img src="${event.target.result}">`
                $('#showCoverImg').append(`<img src="${event.target.result}">`)
            }
        })
        $('#descimgs').change(()=>{
            // console.log('我是敘述圖',descimgs.files)
            let descriptionIMGs = Array.from(descimgs.files); // 轉換為陣列
            descriptionIMGs.forEach((img)=>{
                // console.log('我是敘述圖單張',img)
                let imgreader = new FileReader();
                imgreader.readAsDataURL(img)
                $('#showDescImgs').html('')
                imgreader.onload = (event)=>{
                    $('#showDescImgs').append(`<img src="${event.target.result}">`)
                }
            })
        })
        $('#othersImgs').change(()=>{
            // console.log('othersImgs.files---->',othersImgs.files)
            let othersAllIMGs = Array.from(othersImgs.files); // 轉換為陣列
            othersAllIMGs.forEach((img)=>{
                let imgreader = new FileReader();
                imgreader.readAsDataURL(img)
                $("#showOthersImgs").html('')
                imgreader.onload = (event)=>{
                    $("#showOthersImgs").append(`<img src="${event.target.result}">`)
                }
            })
        })
        
        // 查詢 option 列出的類別
        // fetch('http://localhost/testpet/public/product_back/info/select',{
        $.get('http://localhost/happypet/happypet_back/public/api/product_back/info/categories',({categories})=>{
            // console.log('=========>',res)
            categories.forEach((category) => {   //多張照片遍歷
                // console.log(category.split('-'))  //分割後取英文([0])
                categoryOptions.innerHTML += `<option value="${category.split('-')[0]}">${category}</option>`
            });
        })
        // fetch('http://localhost/happypet/happypet_back/public/api/product_back/info/categories',{
        //     method:'get',
        // })
        // .then(response=>{
        //     return response.json()
        // })
        // .then(({categories})=>{
        //     console.log('我是options',categories)  
        //     categories.forEach((category) => {   //多張照片遍歷 
        //         // console.log(category.split('-'))  //分割後取英文([0])
        //         categoryOptions.innerHTML += `<option value="${category.split('-')[0]}">${category}</option>`
        //     });
        // })


        insertBtn.addEventListener('click', (event)=>{
            event.preventDefault();
            let formData = new FormData(document.getElementById('pdInfo'));
            // formData.append('action', 'insert');
            console.log('formdata',formData)
            formData.forEach((value, key) => {
                console.log('formdata',key, value);
            });
            if(categoryOptions.value === 'default'){
                alert('請選擇類別')
            }
            if($('#pdSeries').value === ''){
                alert('系列編號未填寫')
            }
            // console.log('=======>',)

            fetch('http://localhost/happypet/happypet_back/public/api/product_back/info/create',{
                method:'post',
                body:formData
            })
            .then(response=>{
                if (!response.ok) {
                    throw new Error(`伺服器錯誤: ${response.statusText}`);
                }
                return response.json()
            })
            .then(data=>{
                // alert(data)
                
                console.log('我是data1',data)
                console.log('我是data1',data.message)
                if (data.message) {
                    console.log(data.message);
                    showMsg(data.message)
                    setTimeout(()=>{
                        location.reload()// 刷新頁面
                    },2000)
                } else if (data.error) {
                    console.log('data.error',data.error)
                    showMsg(data.error)
                }
          
            })
        })
    }